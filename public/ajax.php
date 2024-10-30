<?php
    add_action ('wp_ajax_nopriv_kmlg_subscribe', 'kmlgSubscribe') ;
    add_action ('wp_ajax_kmlg_subscribe', 'kmlgSubscribe') ;

    function kmlgSubscribe(){
        global $wp_registered_widgets,$wpdb,$kmlg_url,$kmlg_subs_table_name,$kmlg_shortcode_widgets;
        $settings=get_option('Kloudymail');

        $captcha=wp_remote_post("https://www.google.com/recaptcha/api/siteverify", array(
            'body'=>array(
                'secret'=>$settings['recaptcha_secret_key'],
                'response'=>$_POST['captcha'],
                'remoteip'=>$_SERVER['REMOTE_ADDR']
            )
        ));
        $captchaCheck=json_decode($captcha['body']);

        if($captchaCheck->success){ 
            $url=$kmlg_url;
            $account=$settings['account'];
            $kmlg_key=$settings['api_key'];

            $error=0; // di default da un errore generico

            if (strpos($_POST['id'], 'short')){ // shortcode
                $id = str_replace('form_kloudymail-short-', '', $_POST['id']);

                if (is_numeric($id)){
                    $sql = $wpdb->prepare("SELECT * FROM $kmlg_shortcode_widgets WHERE id='%d'", array($id));
                    $ret = $wpdb->get_row($sql);
                    $notify = (bool)$ret->notify;
                    $list = $ret->list;
                    $listName = $ret->listName;
                    $fields = json_decode($ret->fields);
                } else {
                    wp_die();
                }
            } else {
                $id = str_replace('form_','',$_POST['id']);

                if (preg_match('/^kloudymail-[0-9]+$/', $id) && is_numeric($_POST['number'])){
                    $widget=$wp_registered_widgets[$id];
                    $widget_instances = get_option( $widget['callback'][0]->option_name );
                    $widget_instance = $widget_instances[ $_POST['number'] ];
                    $list=$widget_instance['list'];
                    $listName=$widget_instance['listName'];
                    $notify=$widget_instance['notify'];
                    $fields=json_decode($widget_instance['fields']);
                }else{
                    wp_die();
                }
            }

            $data=$_POST['data'];

            $allFieldsPresent=true;
            foreach ($data as $dataKey => $dataValue) {//validate $data
                $fieldPresent=false;
                foreach ($fields as $fieldsKey => $fieldsValue) {                 
                    if ($dataKey==$fieldsValue->variable){//if fields are present
                        switch ($fieldsValue->type) {
                            case 20://numeric fields
                                if (isset($_POST['numericFields']) && is_array($_POST['numericFields'])){
                                    foreach ($_POST['numericFields'] as $field) {
                                        if ($dataKey==$field){//field is numeric
                                            if ($dataValue!="NaN" && is_numeric($dataValue))//only if value is set and is numeric
                                                $data[$dataKey]=(int)$dataValue;
                                            else
                                                $data[$dataKey]="";
                                        }
                                    }
                                }
                                break;

                            case 40://radio
                                $fieldCorrect=false;
                                foreach ($fieldsValue->options as $option) {
                                    if ($dataValue==$option->value)
                                        $fieldCorrect=true;
                                }
                                if (!$fieldCorrect)
                                    $data[$dataKey]="";//delete field, field is wrong
                                break;

                            case 50://checkbox
                                foreach ($dataValue as $key=>$value) {
                                    $fieldCorrect=false;
                                    foreach ($fieldsValue->options as $option) {
                                        if ($value==$option->value)
                                            $fieldCorrect=true;
                                    }
                                    if (!$fieldCorrect)
                                        unset($data[$dataKey][$key]);//delete non-existent options
                                }                                
                                break;

                            case 60://telephone numbers
                                if (!preg_match('%^[+]?[0-9()/ -]*$%', $dataValue))
                                    $error=2;
                                break;

                            case 70://date
                                $date=explode("-", $dataValue);
                                if (sizeof($date)!=3 || !checkdate($date[1], $date[2], $date[0]))
                                    $data[$dataKey]="";//invalid date
                                break;
                            
                            default:
                                break;
                        }

                        if ($fieldsValue->required){
                            if ($dataValue!="")
                                $fieldPresent=true;
                        }else
                            $fieldPresent=true;
                    }
                }
                if (!$fieldPresent)
                    $allFieldsPresent=false;

            }

            if (!$allFieldsPresent)
                $error=2;

            if ($error==0){

                if (is_email($_POST['address']))
                    $address=sanitize_email($_POST['address']);
                else
                    wp_die();

                $body=array(
                    'address'=> $address,
                    'data'=>$data,
                    'notify'=>$notify
                );

                $lib=new KmApi($kmlg_key,$kmlg_url);
                $ret=$lib->subscriber_insert($account,$list,json_encode($body));

                $insert=array(
                            'address'=>$address,
                            'data'=>json_encode($data),
                            'account'=>$account,
                            'list'=>$list,
                            'listName'=>$listName,
                            'notified'=>$notify
                        );
                
                $retCode=$lib->code;
                switch($retCode){
                    case '0': // errore di connessione
                        $sql = $wpdb->prepare("SELECT address,account,list from $kmlg_subs_table_name where address='%s' AND account='%s' AND list='%s'", array($address,$account,$list) );
                        $wpdb->get_results($sql);

                        if ($wpdb->num_rows>0){ // è già stato inserito
                            $error=1;
                        }else{
                            $insert['sent']=false;
                            $wpdb->insert($kmlg_subs_table_name,$insert);
                            $error=-1;
                        }
                        $retCode=500; // aggiusta il codice di ritorno
                        break;
                    case '500': // errore generico, inserisce lo stesso
                    case '502':
                        $error=-1;
                        $insert['sent']=false;
                        $wpdb->insert($kmlg_subs_table_name,$insert);
                        break;
                    case '400': // dati sbagliati, non inserisce
                        if (isset($ret['errors'])){
                            $retError=$ret['errors'];
                            if ($retError=='Address already exists')
                                $error=1;
                        }else{
                            $error=2;
                        }
                        break;
                    case '401': // errore di configurazione del plugin
                    case '404':
                        $error=0;
                        break;
                    case '200': // tutto a posto, dati inviati
                    case '201':
                        $error=-1;
                        $insert['sent']=true;
                        $sql = $wpdb->prepare("SELECT address,account,list from $kmlg_subs_table_name where address='%s' AND account='%s' AND list='%s'", array($address,$account,$list) );
                        $wpdb->get_results($sql);
                        if ($wpdb->num_rows==0) // non è già stato inserito
                            $wpdb->insert($kmlg_subs_table_name,$insert);
                        break;
                }
            }else{
                $error=2;
                $retCode=400;
            }

            if ($error!=-1){
                wp_send_json_error($error,$retCode);
            }else{
                wp_send_json_success($error);
            }
        }else
            wp_send_json_error('3',500);
    }
    /*
    error:
    -1: tutto a posto
    0: errore generico
    1: già iscritto
    2: dati errati
    3: captcha errato
    */