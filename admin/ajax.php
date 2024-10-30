<?php
    add_action ('wp_ajax_kmlg_export', 'kmlgExport') ;

    add_action ('wp_ajax_kmlg_get_accounts', 'kmlgGetAccounts') ;

    add_action ('wp_ajax_kmlg_get_lists', 'kmlgGetLists') ;

    add_action ('wp_ajax_kmlg_get_fields', 'kmlgGetFields') ;

    add_action ('wp_ajax_kmlg_save_widget', 'kmlgSaveWidget') ;

    add_action ('wp_ajax_kmlg_short_update_fields', 'kmlgShortUpdateFields') ;

    function kmlgExport(){
        if (isset($_GET['selectedAccount']) && isset($_GET['selectedList']) &&
            preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $_GET['selectedAccount']) &&
            preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $_GET['selectedList'])){

            global $wpdb,$kmlg_url,$kmlg_subs_table_name;
            $url=$kmlg_url;
            $settings=(array)get_option('Kloudymail');
            $account=$_GET['selectedAccount'];
            $key=$settings['api_key'];
            $list=$_GET['selectedList'];

            $sql = $wpdb->prepare("SELECT address,data,listName from $kmlg_subs_table_name where account='%s' AND list='%s'", array($account, $list) );
            $ret=$wpdb->get_results($sql,ARRAY_A);

            $lib=new KmApi($key,$kmlg_url);
            $campi=$lib->list_field_list($account,$list,0,100);
            // scarica l'elenco dei campi per poter salvare il csv

            $headerRow=array(
                0 => 'address',
            );

            if (!is_null($campi))//se ci sono campi oltre all'email
                foreach ($campi as $key => $value) {
                    $headerRow[$key+1]=$value['variable'];
                }

            $dataRows=array();
            foreach ($ret as $sub) {     
                $data=json_decode($sub['data']);
                $row=array();
                foreach ($headerRow as $field) {
                    if($field=='address')
                        $row['address']=$sub['address'];
                    else{
                        if(isset($data->$field)){
                            if (is_array($data->$field)){ // se è un campo a scelta multipla
                                $row[$field]=implode('|', $data->$field);
                            }
                            else
                                $row[$field]=$data->$field;
                        }else{
                            $row[$field]='-'; // campo vuoto
                        }
                    }
                }
                array_push($dataRows, $row);
            }
            // print_r($dataRows);

            $fh = @fopen( 'php://output', 'w' );
            fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Content-Description: File Transfer' );
            header( 'Content-type: text/csv' );
            header( "Content-Disposition: attachment; filename=\"".$ret[0]['listName'].".csv\"" );
            header( 'Expires: 0' );
            header( 'Pragma: public' );
            fputcsv( $fh, $headerRow ,';');
            foreach ( $dataRows as $data_row ) {
                fputcsv( $fh, $data_row,';');
            }
            fclose($fh);
        }

        wp_die();
    }

    function kmlgGetAccounts(){
        global $kmlg_url;

        if(isset($_GET['api_key'])){
            $key=$_GET['api_key'];
            $lib=new KmApi($key,$kmlg_url);

            $ret=json_encode($lib->accounts_list(0,100));
            $code=$lib->code;
        }else{
            $ret='';
            $code=400;
        }
        if ($code=='200')
            wp_send_json_success($ret);
        else
            wp_send_json_error($ret,$code);
    }   

    function kmlgGetLists(){
        global $kmlg_url;

        $settings=(array)get_option('Kloudymail');
        $key=$settings['api_key'];
        $lib=new KmApi($key,$kmlg_url);

        $ret=json_encode($lib->list_list($settings['account'],0,100));

        $code=$lib->code;

        if ($code=='200')
            wp_send_json_success($ret);
        else if ($code=='0') // connessione falita
            wp_send_json_error($ret,500);
        else
            wp_send_json_error($ret,$code);
    }

    function kmlgGetFields($list){
        global $kmlg_url;

        $settings=(array)get_option('Kloudymail');
        $key=$settings['api_key'];
        $lib=new KmApi($key,$kmlg_url);

        return json_encode($lib->list_field_list($settings['account'],$list,0,100));
    }

    function kmlgSaveWidget(){
        global $wpdb,$kmlg_shortcode_widgets;

        $settings=(array)get_option('Kloudymail');

        if ((is_numeric($_POST['id']) || $_POST['id']=='add') && preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $_POST['list'])){
            $id=$_POST['id'];
            $list=$_POST['list'];

            $name=substr(sanitize_text_field($_POST['name']),0,30);//max 30 characters
            $listName=substr(sanitize_text_field($_POST['listName']),0,30);//max 128 characters

            if ($name!="" && $listName!=""){//not empty
                $insert=array(
                    'name'=>$name,
                    'account'=>$settings['account'],
                    'list'=>$list,
                    'listName'=>$listName,
                    'fields'=>kmlgGetFields($list),
                    'notify'=>$_POST['notify']=='true',
                );

                if ($id == 'add'){
                    $wpdb->insert($kmlg_shortcode_widgets,$insert);
                } else {
                    $wpdb->update($kmlg_shortcode_widgets,$insert,array('id'=>$id));
                }
            }
        }
        wp_die();
    }

    function kmlgShortUpdateFields(){
        global $wpdb,$kmlg_shortcode_widgets;

        $settings=(array)get_option('Kloudymail');

        if (is_numeric($_POST['id'])){
            $id=(int)($_POST['id']);
            
            $sql = $wpdb->prepare("SELECT * FROM $kmlg_shortcode_widgets WHERE id='%d'", array($id));
            $ret=$wpdb->get_row($sql);
            // If id exits and account is the same
            if (!is_null($ret) && $ret->account==$settings['account']){
                $list=$ret->list;

                $wpdb->update($kmlg_shortcode_widgets,array('fields'=>kmlgGetFields($list)),array('id'=>$id));
            }
        }    

        wp_die();
    }
?>