<?php
include_once("../integrated/setup.php");
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
$collector = new JsonCollector(
  targetLanguage: $OfferApi->targetLanguage,
  pageName: "checkout"
);

$country="tr";
?>
<style>
#fields_city,
#fields_address2{
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23343a40' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 9px 5px;
    padding-right: 32px;
    text-transform: capitalize;
}
</style>
<div class="row mb-n3 align-items-start mb-3">
        <div class="col-sm-6 mb-3">
            <div class="mb-3">
                <label class="p cart-input-label" for="state" label="State/Province"><?= $collector->translate("state_new_".$country, "Province"); ?></label>
                <div>
                    <select id="fields_state" name="state" class="cart-input p" toSearch="state_code" toenableel = "fields_city">
                        <option value=""><?= $collector->translate("p_state_new_".$country, "Select Province"); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6 pl-sm-2 mb-3">
            <div class="mb-3 ">
                <label class="p cart-input-label" for="district" label="district"><?=$collector->translate("district_".$country, "District"); ?></label>
                <div class="self-autocomplete">
                    <input id="fields_city" toSearch="city" 
                                        name="district" 
                                        class="cart-input p search" value="" type="text"                 
                                        disabled trigger="click focus keyup"  
                                        toenableel="fields_address2"
                                        onEnabled="<?= $collector->translate("p_district_onenabled".$country, "Select District",false); ?>"
                                        onPlaceholder="<?= $collector->translate("p_district_new".$country, "Select province first",false); ?>"
                                        placeholder="<?= $collector->translate("p_district_new".$country, "Select province first",false); ?>"/>
                                    <div class="self-suggestions" style="display:none;"></div>  
                    
                </div>
            </div>
        </div>
</div>

<div class="row align-items-start mb-3">
    <div class="col-sm-12">
        <div class="row mb-n3 align-items-start ">
            <div class="col-sm-8">
                <div class="mb-3">
                    <label class="p cart-input-label" for="address 2" label="Neighborhood"><?=$collector->translate("neighborhood_".$country, "Neighborhood"); ?></label>
                    <div class="self-autocomplete">
                         <input id="fields_address2" toSearch="address2" 
                            name="address2" 
                            class="cart-input p search" value="" type="text" 
                            disabled
                            trigger="click focus keyup"  
                            onEnabled="<?= $collector->translate("p_neighborhood_onenabled".$country, "Select Neighborhood",false); ?>"
                            onPlaceholder="<?= $collector->translate("p_neighborhood_new_".$country, "Select district first",false); ?>"
                            placeholder="<?= $collector->translate("p_neighborhood_new_".$country, "Select district first",false); ?>"
                            linkElement="fields_zip"  
                            required="required"/>
                           <div class="self-suggestions" style="display:none;"></div>  
                       
                    </div>
                </div>
            </div>
            <div class="col-sm-4 pl-sm-2  ">
                <div class="mb-3">
                    <label class="p cart-input-label" for="zip" label="Zip/Postal Code"><?= $collector->translate("zip_new_".$country, "Postal Code"); ?></label>
                    <div>
                        <input id="fields_zip" name="zip" class="cart-input p" value="" type="text" placeholder="<?= $collector->translate("p_zip_new_".$country, "Postal Code"); ?>" required="required">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="address 1" label="Street, house and apartment number"><?=$collector->translate("address_1_new_".$country, "Full Address") ?></label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" value="" type="text" placeholder="<?=$collector->translate("p_address_1_new_".$country, "Street/Road, building number, floor, apartment"); ?>" required="required">
    </div>
</div>

 <!-- <div class="mb-3">
    <label class="p cart-input-label" for="fields_tax_id" label="turkish ID"><?=$collector->translate("tax_id_".$country, "Turkish National Identity Number (optional)"); ?></label>
    <div>
        <small class="p" style="display:block; color:#6b7280;"><?= $collector->translate("note_tax_id_".$country, "Turkish National Identity Number or Foreign National Identity Number — 11 digits"); ?></small>
        <input id="fields_tax_id" optional name="tax_id" class="cart-input p" value="" type="text" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="11" minlength="11" required="required">
        <small class="p" style="display:block; color:#6b7280; margin-top:4px;"><?= $collector->translate("help_tax_id_".$country, "It may be required for customs procedures."); ?></small>
    </div>
</div>  -->

<script>
(function () {  
    var state = document.getElementById('fields_state');
  
    state.addEventListener('change', function (event) {
        var zipcode = document.getElementById('fields_zip');
        var city = document.getElementById('fields_city');
        var address2 = document.getElementById('fields_address2');
        zipcode.value="";
        city.value="";
        address2.value="";
        
        if(state.value!==""){
            city.removeAttribute("disabled");
            city.placeholder = city.getAttribute("onenabled")
        }else{
            city.setAttribute("disabled","");
            city.placeholder = city.getAttribute("onplaceholder");
        }
    });

})();
</script>




