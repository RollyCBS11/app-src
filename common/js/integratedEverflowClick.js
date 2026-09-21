(function () {
    var FormEf = function (config) {       
        
        var form_ef = {}; 
        form_ef._kn={};     
        
        window.getQueryStringByName=function (name) {
                    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
                    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                        results = regex.exec(location.search);
                    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " ")).trim();
        }

        window.extractDomain = function (url) {
                    var domain;
                    if (url.indexOf("://") > -1) {
                        domain = url.split('/')[2];
                    }
                    else {
                        domain = url.split('/')[0];
                    }
                    return domain.trim();
        }

        window.getCurrentOffer = function () {
                    var currentOffer = window.location.pathname.substr(0, window.location.pathname.lastIndexOf('/')).toLowerCase().replace('/mobile', '');
                    var pos = currentOffer.lastIndexOf('/');
                    currentOffer = currentOffer.substr(pos).replace("/", "").trim();
                    if (currentOffer == extractDomain(window.location.pathname)) {
                        currentOffer = '';
                    }
                    return currentOffer;
        }    

        //get data in the offer_{offer_name} in the session storage
        form_ef.getDataStorage= function(){             
             try {
                var data= sessionStorage.getItem("offer_" + getCurrentOffer());
                if(data==null){
                    return {};
                }else{
                    return JSON.parse(data);
                }               

             } catch (error) {
                return {};
             }
        }

        //get specific proper value in the offer_{offer_name} in the session storage
        form_ef.getDataPropertyValue=function(prop){
            var data=form_ef.getDataStorage();
            if(data.hasOwnProperty(prop)){
                return data[prop];
            }else{
                return "";
            }
        }

        //set specific proper value in the offer_{offer_name} in the session storage
        form_ef.setDataPropertyValue=function(prop,value){
            var data=form_ef.getDataStorage();
                data[prop]=value;
                sessionStorage.setItem("offer_"+getCurrentOffer(),JSON.stringify(data));
        }

        
        form_ef.triggerEFClick = function(param){
           
            if(typeof EF!="undefined" && param.transaction_id==""){
                console.log("EF is loaded..");
                if (param.hasOwnProperty("affid2") && sessionStorage.getItem("Adv_transId_"+getCurrentOffer())==null) {
                    EF.click({
                            tracking_domain: "https://www.smartbuy4u.club",
                            offer_id: param.oid2,
                            affiliate_id: param.affid2,
                            sub1: param.sub1,
                            sub2: param.sub2,
                            sub3: param.sub3,
                            sub4: param.sub4,
                    }).then(function(transaction_id){

                            if(transaction_id!=""){
                                sessionStorage.setItem("Adv_offerId_"+getCurrentOffer(),EF.urlParameter("oid2"));
                                sessionStorage.setItem("Adv_transId_"+getCurrentOffer(),transaction_id);
                            }

                            EF.click({
                                tracking_domain: "https://www.b04jdmd.com",
                                offer_id: param.oid,
                                affiliate_id: param.affId,
                                sub1: param.sub1,
                                sub2: param.sub2,
                                sub3: param.sub3,
                                sub4: param.sub4,
                                sub5: transaction_id,
                                uid: param.uid,
                                source_id: param.source_id,
                                transaction_id: param.transaction_id
                            }).then(function (transactionId) {                
                                    // transactionId containts the unique Everflow transaction ID
                                    if (param.transaction_id == '') {                 
                                            var data = form_ef.getDataStorage();
                                            if (Object.keys(data).length!=0) {
                                                data.transaction_id = transactionId;
                                                sessionStorage.setItem("offer_" + getCurrentOffer(), JSON.stringify(data));
                                            }
                                    }
                            });
                    });
                } else {
                        EF.click({
                            tracking_domain: "https://www.b04jdmd.com",
                            offer_id: param.oid,
                            affiliate_id: param.affId,
                            sub1: param.sub1,
                            sub2: param.sub2,
                            sub3: param.sub3,
                            sub4: param.sub4,
                            sub5: param.sub5,
                            uid: param.uid,
                            source_id: param.source_id,
                            transaction_id: param.transaction_id
                        }).then(function (transactionId) {                
                            // transactionId containts the unique Everflow transaction ID
                            if (param.transaction_id == '') {                 
                                var data = form_ef.getDataStorage();
                                if (Object.keys(data).length!=0) {
                                    data.transaction_id = transactionId;
                                    sessionStorage.setItem("offer_" + getCurrentOffer(), JSON.stringify(data));
                                }
                            }
                        });
                }
            }else{
                setTimeout(function(){
                    console.log("EF not yet loaded..");                    
                    form_ef.triggerEFClick(((Object.keys(form_ef._kn).length!=0) ? form_ef._kn : form_ef.getDataStorage()));
                },300)
            }
            
        }

        form_ef.getKonnektiveAndEFParams=function() {
                if(getQueryStringByName('transaction_id')!=""){
                    return;
                }
            
                if (getQueryStringByName('affId') != '' || getQueryStringByName('affid') != '') {
                    form_ef._kn = {
                        affId: getQueryStringByName('affId') || getQueryStringByName('affid'),
                        transaction_id: getQueryStringByName('transaction_id'),
                        c1: getQueryStringByName('c1'),
                        c2: getQueryStringByName('c2'),
                        c3: getQueryStringByName('c3'),
                        c4: getQueryStringByName('c4'),
                        c5: getQueryStringByName('c5')
                    }

                    //add everflow tracking parameters thru oid reserve paramater
                    if(getQueryStringByName("offer_id")!="" || getQueryStringByName("oid")!=""){
                        form_ef._kn.oid=getQueryStringByName("oid") || getQueryStringByName('offer_id');
                       
                        if (getQueryStringByName('sub1') != '') {
                            form_ef._kn.sub1 = getQueryStringByName('sub1');
                        }
                        if (getQueryStringByName('sub2') != '') {
                            form_ef._kn.sub2 = getQueryStringByName('sub2');
                        }
                        if (getQueryStringByName('sub3') != '') {
                            form_ef._kn.sub3 = getQueryStringByName('sub3');
                        }
                        if (getQueryStringByName('sub4') != '') {
                            form_ef._kn.sub4 = getQueryStringByName('sub4');
                        }
                        if (getQueryStringByName('sub5') != '') {
                            form_ef._kn.sub5 = getQueryStringByName('sub5');
                        }
                        if (getQueryStringByName('uid') != '') {
                            form_ef._kn.uid = getQueryStringByName('uid');
                        }
                        if (getQueryStringByName('source_id') != '') {
                            form_ef._kn.source_id = getQueryStringByName('source_id');
                        } 
                        if (getQueryStringByName('oid2') != '') {
                            form_ef._kn.oid2 = getQueryStringByName('oid2');
                        } 
                        if (getQueryStringByName('affid2') != '' || getQueryStringByName('affId2') !="") {
                            form_ef._kn.affid2 = getQueryStringByName('affid2') || getQueryStringByName('affId2');
                        }   
                        
                        form_ef._kn.click_ef=true;
                    }
                    
                    form_ef.triggerEFClick(form_ef._kn);//trigger everflow click event
                    sessionStorage.setItem("offer_" + getCurrentOffer(), JSON.stringify(form_ef._kn));
                   
                }else if(Object.keys(form_ef.getDataStorage()).length==0){
                    sessionStorage.setItem("offer_" + getCurrentOffer(), JSON.stringify({}));
                } else if(form_ef.getDataPropertyValue("transaction_id")=="" && form_ef.getDataPropertyValue("affId")!="" && Object.keys(form_ef.getDataStorage()).length!=0){
                    form_ef.triggerEFClick(form_ef.getDataStorage());//trigger everflow click event
                }
        }  

        //initiatialize execution of form events code
        //you create a callback function if you want to initiate or execute script upon calling form_ef.exec();
        form_ef.init = function (callback) {              
              form_ef.getKonnektiveAndEFParams(); //set konnektive params and Everflow params 
               if(typeof callback=="function"){
                    callback();
                }
        }

        return form_ef;
    }

    var init = 'CRMIntegratedEverflow';
    if (typeof window[init] !== 'function') {
        window[init] = FormEf;
    }
})();

var CRMIntegratedEverflow = CRMIntegratedEverflow();
CRMIntegratedEverflow.init();
