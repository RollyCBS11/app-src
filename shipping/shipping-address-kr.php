<?php
if (!isset($OfferApi)) { include_once("../integrated/setup.php"); }
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
$collector_sh = new JsonCollector(
  targetLanguage: $OfferApi->targetLanguage,
  pageName: "checkout-shipping"
);
$country="_kr";
?>

<div class="field_address_wrapper">
    <div class="row mb-3">
        <div class="col-5 pr-0">
            <label class="p cart-input-label">&nbsp;</label>       
            <div class="fields-zip-wrapper">
                <input id="btn-search-address" class="btn-block px-2 py-2 btn btn-pay" style="font-weight: bold" type="button" value="<?= $collector_sh->translate("search_address_korea_b".$country, "Search Address"); ?>">            
            </div>
        </div>
        <div class="col-7">
            <label class="p cart-input-label" for="zip" label="Zip/Postal Code"><?= $collector_sh->translate("zip_postal_korea_b".$country, "Postal Code"); ?></label>       
            <div class="fields-zip-wrapper">
                <input id="fields_zip" name="zip" class="cart-input p" readonly value="" type="text" placeholder="<?= $collector_sh->translate("p_zip_korea_b".$country, "Postal Code"); ?>">
            
            </div>
        </div>
        
    </div>    
    <div id="address-suggest">
        <a href="javascript:void(0);" id="close-suggestion">X</a>
        <div id="content">
            <div class="loading p-2"><?= $collector_sh->translate("loading", "loading..."); ?></div>
        </div>
        
    </div>   
</div>


<div class="mb-3" style="display:none;" <?//=(isset($_GET['sub10']) && $_GET['sub10']=="checkout-b") ? "style='display:none;'" : "";?>>
    <label class="p cart-input-label" for="city" label="province/City"><?=$collector_sh->translate("province_city_korea".$country, "Province / City"); ?></label>
    <div>
        <input id="fields_city" name="city" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_province_city".$country, "Province / City"); ?>">
    </div>
</div>

<div class="mb-3" style="display:none;" <?//=(isset($_GET['sub10']) && $_GET['sub10']=="checkout-b") ? "style='display:none;'" : "";?>>
    <label class="p cart-input-label" for="state" label="State/Province"><?= $collector_sh->translate("state_district_korea".$country, "District"); ?></label>
    <div>
        <input id="fields_state" name="state" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("input_district_korea".$country, "District"); ?>">       
    </div>
</div>

<div class="mb-3 mt-2">
    <label class="p cart-input-label" for="address 1" label="Address Line 1">
        <?php if(isset($_GET['sub10']) && $_GET['sub10']=="checkout-b"){ 
                    echo $collector_sh->translate("address__korea_b".$country, "Address Line 1");
                }else{
                    echo $collector_sh->translate("address__korea".$country, "Address Line 1");
                }
        ?>
    </label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" readonly value="" type="text" autocomplete="off" placeholder="<?=(isset($_GET['sub10']) && $_GET['sub10']=="checkout-b")? $collector_sh->translate("address_1_korea_b", "Search for an address"):  $collector_sh->translate("address_1_korea".$country, "Road name & building number"); ?>">
         
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="address 2" label="Building Name, Room Number, Company Dept."><?=(isset($_GET['sub10']) && $_GET['sub10']=="checkout-b")? $collector_sh->translate("address_2_korea_b".$country, "Address Line 2"):$collector_sh->translate("address_2_korea".$country, "Address Line 2"); ?></label>
    <div>
        <input id="fields_address2" name="address 2" class="cart-input p" value="" type="text" placeholder="<?=(isset($_GET['sub10']) && $_GET['sub10']=="checkout-b")? $collector_sh->translate("address_2_p_korea_b".$country, "Apartment/ Unit/ Floor"):$collector_sh->translate("address_2_p_korea".$country, "Apartment, Unit, Floor");?>">
    </div>
</div>



<div class="mb-3">
    <label class="p cart-input-label" for="fields_pccc" label="">
        <?=$collector_sh->translate("pccc_label__korea".$country, "PCCC (Personal Customs Clearance Code)"); ?>
        <button type="button" id="pccc-info-btn" aria-label="PCCC info" class="tw-ml-1 tw-inline-flex tw-items-center tw-justify-center tw-w-4 tw-h-4 tw-rounded-full tw-bg-gray-400 tw-text-white tw-text-[10px] tw-font-bold tw-leading-none tw-cursor-pointer tw-border-0 tw-align-middle">i</button>
    </label>
    <div class="relative" style="position: relative; width: 100%;">
    <input id="fields_pccc" 
           name="fields_pccc" 
           class="cart-input p" 
           value="" 
           type="text" 
           placeholder="<?= $collector_sh->translate("pccc__korea".$country, "Personal Customs Clearance Code"); ?>"
           style="width: 100%; padding: 12px 40px 12px 16px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 16px; line-height: 1.5; background-color: #ffffff; transition: all 0.15s ease;">
    
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" 
         style="position: absolute; top: 0; bottom: 0; right: 0; display: flex; align-items: center; padding-right: 12px; pointer-events: none;">
        <svg class="w-5 h-5 text-gray-400" 
             style="width: 20px; height: 20px; color: #9ca3af; transition: color 0.2s ease;"
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>
    <div>
    <div id="pccc-helper" class="tw-hidden tw-mt-2 tw-p-3 tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-lg tw-text-sm tw-text-gray-700 tw-leading-relaxed">
        <?php if ($OfferApi->targetLanguage === 'ko'): ?>
        <p class="tw-font-semibold tw-mb-1">개인통관고유부호 (PCCC)</p>
        <p class="tw-mb-1">해외직구 시 관세청에서 반드시 필요한 13자리 코드(P로 시작)입니다.</p>
        <p class="tw-mb-2">걱정 마세요! 대부분의 고객님들이 이미 가지고 계시며, 1분 만에 발급 가능합니다.</p>

        <p>DDP로 배송합니다 — 모든 관세와 세금은 저희가 부담합니다.</p>
        <a href="https://unipass.customs.go.kr/per/persIndex.do" target="_blank" rel="noopener noreferrer" class="tw-font-semibold tw-text-blue-600 tw-underline">바로 발급받기 &rarr;</a>
        <?php else: ?>
        <p class="tw-font-semibold tw-mb-1">Personal Customs Clearance Code (PCCC)</p>
        <p class="tw-mb-1">This is a required 13-digit code (starts with "P") for all international shipments to South Korea.</p>
        <p class="tw-mb-2">Don't worry — it's quick and easy! Most customers already have one.</p>

        <p>We ship DDP — all taxes and duties are paid by us.</p>
        <a href="https://unipass.customs.go.kr/per/persIndex.do" target="_blank" rel="noopener noreferrer" class="tw-font-semibold tw-text-blue-600 tw-underline">Get yours instantly &rarr;</a>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('fields_pccc');
    var helper = document.getElementById('pccc-helper');
    var btn = document.getElementById('pccc-info-btn');

    function show() { helper.classList.remove('tw-hidden'); }
    function hide() { if (document.activeElement !== input) helper.classList.add('tw-hidden'); }

    input.addEventListener('focus', show);
    input.addEventListener('blur', function () { setTimeout(hide, 150); });
    btn.addEventListener('click', function () { helper.classList.toggle('tw-hidden'); });

    //start kakao postal code    
            var layer = document.getElementById("address-suggest");

            function closePostcode() {                
                layer.style.display = "none";
            }
      
            document.getElementById("btn-search-address").addEventListener("click", function(){
                //alert("hello");
                layer.style.display = "block";
                var postcode =new daum.Postcode({
                    oncomplete(data) {
                        document.getElementById("fields_zip").value = data.zonecode;
                        document.getElementById("fields_state").value = data.sido;
                        document.getElementById("fields_city").value = data.sigungu;

                        if(data.buildingName!=""){
                            document.getElementById("fields_address1").value = data.roadAddress+" ("+data.buildingName+")";
                        }else{
                            document.getElementById("fields_address1").value = data.roadAddress;
                        }
                        document.getElementById('fields_address2').focus();
                        closePostcode()
                    },
                    onclose() {
                        closePostcode();
                    },
                    width: "100%",
                    height: "100%",
                });
                postcode.embed(layer);

            });

            document.getElementById("close-suggestion").addEventListener("click",function(){
                closePostcode();
            });

            // Wait until the iframe is inserted and loaded
                var observer = new MutationObserver(function(){
                    var iframe = layer.querySelector("iframe");

                    if (iframe) {
                        iframe.addEventListener("load", function(){
                            var loading = layer.querySelector(".loading");
                            if (loading) loading.remove();
                        });

                        observer.disconnect();
                    }
                });

                observer.observe(layer, {
                    childList: true
                });

            document.addEventListener("click", function (e){
                var input = document.getElementById("btn-search-address");
                if (
                    !layer.contains(e.target) &&
                    e.target !== input
                ) {
                    closePostcode();
                }
            });

            function resizePostcode() {
                var layer = document.getElementById("address-suggest");

                if (window.innerWidth < 768) {
                    // Mobile
                    layer.style.width = "auto";
                    layer.style.height = "390px";
                } else {
                    // Desktop
                    layer.style.width = "100%";
                    layer.style.height = "390px";
                }
            }

            window.addEventListener("resize", resizePostcode);
            resizePostcode();
            //end kakao postal code


})();
</script>

<?php $collector_sh->saveTranslation(); ?>