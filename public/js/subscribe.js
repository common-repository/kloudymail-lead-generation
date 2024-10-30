jQuery(function($){
	$(document).ready(function(){
		var allFields=new Array();
		var formSubmitted=null;

		$("form[id^=form_kloudymail]").each(function(){
			var form=$(this);

			var subs_obj=window[$(this).data('instance')];
			var key=subs_obj.key;
			var notify=subs_obj.notify;
			var list=subs_obj.list;
			var fieldsList=subs_obj.fields;
			var captchaKey=subs_obj.captchaKey;
			var fields=new Array();
			var submit_on=true;
			var cap='';//per il recaptcha

			var checkboxRequired=new Array();

			if(list!='------'){//se la lista è stata impostata

	        	var fieldsHtml="";

	        	if (fieldsList.length>0){
	        	for(j=0;j<fieldsList.length;j++){
	        		if (fieldsList[j]['visible']){
	        			fieldsHtml+="<label>"+fieldsList[j]['name']+":";
	        			
	        			if (fieldsList[j]['required'])//campo obbligatorio, aggiunge "required" al campo
	        				required="required";
	        			else
	        				required="";
	        			fields_name = form.attr('id')+"_"+fieldsList[j]['variable'];
	            		switch(fieldsList[j]['type']){
	            			case 10:
	            				fieldsHtml+='<input class="kmlg_input_text" type="text" '+required+' data-field="' + fields_name + '"></label>';
	            				break;
	        				case 20:
	        					fieldsHtml+='<input class="kmlg_input_number" type="number" '+required+' data-field="' + fields_name + '"></label>';
	        					break;
	    					case 30:
	        					fieldsHtml+="<textarea class='kmlg_input_textarea' "+required+" data-field=\"" + fields_name + "\"></textarea></label>";
	        					break;
	    					case 40:
	        					fieldsHtml+="</label>";
	        					for(i=0;i<fieldsList[j]['options'].length;i++){
	        						fieldsHtml+="<label class='kmlg_input_label'>"+fieldsList[j]['options'][i]['value']+"<input class='kmlg_input_radio' value='"+fieldsList[j]['options'][i]['value']+"' type='radio' name='"+fieldsList[j]['variable']+"' data-field=\"" + fields_name + "\" "+required+"></label>";
	        					}
	        					fieldsHtml+="<br>";
	        					break;
	    					case 50:
	        					fieldsHtml+="</label>";
	        					for(i=0;i<fieldsList[j]['options'].length;i++){
	        						fieldsHtml+="<label class='kmlg_input_label'>"+fieldsList[j]['options'][i]['value']+"<input class='kmlg_input_checkbox' type='checkbox' value='"+fieldsList[j]['options'][i]['value']+"' name='"+fieldsList[j]['variable']+"' data-field=\"" + fields_name + "\" "+required+"></label>";
	        					}
	        					fieldsHtml+="<br>";
	        					if (required)
	        						checkboxRequired.push(fields_name);
	        					break;
	    					case 60:
	    						fieldsHtml+="<table type='tel' data-field=\"" + fields_name + "\" ><tr><td><select class='kmlg_input_tel_prefix' "+required+">"
    							fieldsHtml+=subs_obj.countryCodes;
    							fieldsHtml+="</select></td></tr>"
	        					fieldsHtml+="<tr><td><input class='kmlg_input_tel' type='tel' pattern='^[0-9()/ -]*$' "+required+"></tr></td></tr></table></label>";
	        					break;
	    					case 70:
	        					fieldsHtml+="<input class='kmlg_input_date' type='date' data-field=\"" + fields_name + "\" "+required+"></label>";
	        					break;
	    					default:
	    						break;
	            		}
	        		}
	        		fields.push(fieldsList[j]['variable']);
	        	}}
	        	fields_name = form.attr('id') + "_email";
	        	form.append("<label>Email:<input type='email' class='kmlg_input_email' data-field=\"" + fields_name + "\" required></label>"+fieldsHtml+"<button class='kmlg_input_submit' type='submit' id='"+form.attr('id')+"_submit'>"+subs_obj.subscribe_text+"</button>");		            	
	        	form.append("<div id='"+form.attr('id')+"_captcha' class='g-recaptcha' data-sitekey='"+captchaKey+"' data-callback='kmlgOnSubmit' data-size='invisible'></div>");
	        	allFields[form.attr('id')]=fields;

	        	form.submit(function(event){
	        		event.preventDefault();
	        		formSubmitted=$(this);
	        		grecaptcha.execute(kmlgGetReCaptchaID(formSubmitted.attr('id')+"_captcha"));
	        	});
	        	window.kmlgOnSubmit=kmlgOnSubmit;
	        	function kmlgOnSubmit(res){
	        		cap=res;
					kmlgSubscribe();
				}

				function kmlgGetReCaptchaID(containerID) {
				    var retval = -1;
				    $(".g-recaptcha").each(function(index) {
				        if(this.id == containerID)
				        {
				            retval = index;
				            return;
				        }
				     });
				 
				     return retval;
				}

				$("input[class='kmlg_input_checkbox']").on('click',function(){
					if ($.inArray($(this).data('field'),checkboxRequired)!=-1){//solo se il campo è obbligatorio
						var ok=false;
						var obj=$(this);//per controllare se il campo è lo stesso
						$("input[class='kmlg_input_checkbox']").each(function(){
							if ($(this).prop("checked") && $(this).data("field")==obj.data("field"))
								ok=true;
						});
						if (ok){
							$("input[class='kmlg_input_checkbox']").each(function(){
								if ($(this).data("field")==obj.data("field"))
									$(this).removeAttr('required');
							});
						}
						else{
							$("input[class='kmlg_input_checkbox']").each(function(){
								if ($(this).data("field")==obj.data("field"))
									$(this).prop('required',true);
							});
						}
					}
				});

				$("select[class='kmlg_input_tel_prefix']").on('change',function(){
					var numberObj=$(this).parent().parent().parent().find("input[class='kmlg_input_tel']");

					if ($(this).val()==""){
						numberObj.prop('required',false);
					}else{
						numberObj.prop('required',true);
					}
				});

				$("input[class='kmlg_input_tel']").on('change',function(){
					
					var prefixObj=$(this).parent().parent().parent().find("select[class='kmlg_input_tel_prefix']");

					if ($(this).val()==""){
						prefixObj.prop('required',false);
					}else{
						prefixObj.prop('required',true);
					}
				});

				function kmlgSubscribe(){
					if (submit_on){
						submit_on=false;
						formSubmitted.find('button.kmlg_input_submit').prop('disabled',true);
						formSubmitted.append("<div class='kmlg_subscribing'></div>");
						formSubmitted.find("div.kmlg_subscribing").show();
						formSubmitted.find("div.kmlg_subscribe_text").remove();
						
						var data=new Object();
						var numericFields=new Array();
						var fields=allFields[formSubmitted.attr('id')],
							obj;
						for(j=0;j<fields.length;j++){
							obj=formSubmitted.find("[data-field=" + formSubmitted.attr('id') + '_' + fields[j] + "]");							
							switch(obj.attr('type')){
								case 'number':
									data[fields[j]]=parseInt(obj.val());
									numericFields.push(fields[j]);
									break;
								case 'radio':
									data[fields[j]]=formSubmitted.find("[data-field=" + formSubmitted.attr('id') + '_' + fields[j] + "]:checked").val();
									break;
								case 'checkbox':
									data[fields[j]]=new Array();

									formSubmitted.find("[data-field=" + formSubmitted.attr('id') + '_' + fields[j] + "]:checked").each(function(){
										data[fields[j]].push($(this).val());			
									});
									break;
								case 'tel':
									var code=obj.find("select").val();
									var phone=obj.find("input").val();

									data[fields[j]]=code+phone;
									break;
								default:
									data[fields[j]]=obj.val();
									break;
							}
						}
						if($.isEmptyObject(data))
							data='';//workaround 

						$.ajax({
					        url : formSubmitted.attr('action'),
					        type : "POST",
					        data: {
					    		action: "kmlg_subscribe",
					    		'address': formSubmitted.find('[data-field=' + formSubmitted.attr('id') + '_email]').val(),
					        	'data': data,
					        	'numericFields': numericFields,
					        	'notify': notify,
					        	'id': formSubmitted.attr('id'),
					        	'number': formSubmitted.find('[name=widget_number]').val(),
					        	captcha: cap
					        },
					        beforeSend: function(request){
					        	request.setRequestHeader('Authorization','bearer '+key);
					        	//request.setRequestHeader('Content-type','application/json');
					        }
					    }).done(function(json){
				        	formSubmitted.trigger("reset");

				        	if(notify)
				        		formSubmitted.append("<div class='kmlg_subscribe_text'>"+subs_obj.subscribed_notify+"</div>");
				        	else
				        		formSubmitted.append("<div class='kmlg_subscribe_text'>"+subs_obj.subscribed+"</div>");
					    }).fail(function(jqXHR){
				        	
				        	var json=jqXHR.responseJSON;

				        	switch(json.data){
				        		case 0:
				        			formSubmitted.append("<div class='kmlg_subscribe_text'>"+subs_obj.error+"</div>")
				        			break;
				        		case 1:
									formSubmitted.append("<div class='kmlg_subscribe_text'>"+subs_obj.errorExists+"</div>");
				        			break;
			        			case 2:
			        				formSubmitted.append("<div class='kmlg_subscribe_text'>"+subs_obj.errorWrong+"</div>");
			        				break;
		        				case 3:
			        				formSubmitted.append("<div class='kmlg_subscribe_text'>"+subs_obj.errorCaptcha+"</div>");
			        				break;
				        	}							
				        }).always(function(){
				        	submit_on=true;
				        	grecaptcha.reset(kmlgGetReCaptchaID(formSubmitted.attr('id')+"_captcha"));
				        	formSubmitted.find('button.kmlg_input_submit').prop('disabled',false);
				        	formSubmitted.find("div.kmlg_subscribing").remove();
			        	});
					}
				}
			}
		});
	});	
});
