<?php
include_once("../integrated/setup.php");
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
$collector = new JsonCollector(
  targetLanguage: $OfferApi->targetLanguage,
  pageName: "checkout"
);

$country="qa";
?>

<div class="mb-3">
    <label class="p cart-input-label" for="state" label="municipality/city"><?= $collector->translate("city_municipality_".$country, "City / Municipality"); ?></label>
    <div>
        <select id="fields_state" name="state" class="cart-input p">
            <option value=""><?= $collector->translate("city_select_".$country, "Select"); ?></option>
        </select>
    </div>
</div>
<!-- Hidden address1, building_no -->
<input type="hidden" id="fields_address1" name="address 1" value="">
<input type="hidden" id="fields_address2"/>
<input type="hidden" id="fields_city"/>
<input type="hidden" id="fields_zip"  value="00000"  />

<div class="row align-items-start mb-3">
    <div class="col-sm-12">
        <div class="row mb-n3 align-items-start ">
             <div class="col-sm-4 pr-sm-2">
                <div class="mb-3 ">
                    <label class="p cart-input-label" for="area" label="Area"><?=$collector->translate("zone_".$country, "Zone"); ?></label>
                    <div>
                        <input id="_area" name="area" el-required class="cart-input p" inputmode="numeric" 
                                pattern="[0-9]*" 
                                  type="text" placeholder="<?= $collector->translate("p_area__".$country, "Ex: 55"); ?>" required="required">
                    </div>
                </div>
            </div>
            <div class="col-sm-4 pr-sm-2  ">
                <div class="mb-3 ">
                    <label class="p cart-input-label" for="Street" label="Street"><?=$collector->translate("street_".$country, "Street"); ?></label>
                    <div>
                        <input id="_street" name="Street" el-required class="cart-input p" value="" type="text" placeholder="<?= $collector->translate("p_street__".$country, "Ex: 850"); ?>" required="required">
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="mb-3">
                    <label class="p cart-input-label" for="building" label="building_no"><?= $collector->translate("building_".$country, "Building"); ?></label>
                    <div>
                        <input id="_building" name="building" el-required  class="cart-input p" value="" type="text" placeholder="<?= $collector->translate("p_building__".$country, "Ex: 25"); ?>" required="required">
                    </div>
                </div>
            </div>
          
           
        </div>
        <small><?=$collector->translate("helper_text_new_zone_".$country, "The zone, street and building numbers are on the blue plate outside the building, or search free at <a class='tw-text-[#387FF5]' target='_blank' href='https://qnas.qa'>qnas.qa</a>");?></small>
    </div>
</div>
<div class="mb-3">
    <label class="p cart-input-label" for="address 2" label="Unit / Floor / Apartment"><?=$collector->translate("unit__".$country, "Unit / Floor / Apartment (optional)") ?></label>
    <div>       
        <input id="_unit" name="address 2" class="cart-input p" value="" type="text" placeholder="<?=$collector->translate("p_unit__".$country, "Ex: Apartment 12, 4th floor"); ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="street name" label="Street Name"><?=$collector->translate("street_name__".$country, "Street Name/ Area (optional)"); ?></label>
    <div>
        <input id="_street_name" name="street name" class="cart-input p" value="" type="text" placeholder="<?=$collector->translate("p_street_newname__".$country, "Ex: Al Sadd");?>">
        <small><?=$collector->translate("hint_street_name__".$country, "If your street has a name, this helps the delivery person.");?></small>
    </div>
</div>

<script>
(function () {

    // Zone: keep Western digits only (accept Arabic-Indic input and normalize)
    var arabicDigits = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
     document.getElementById('_area').addEventListener('input', function () {
    this.value = this.value.replace(/[٠-٩]/g, function (d) { return arabicDigits[d]; }).replace(/\D/g, '');
  });

    function mergeAddress1() {
        var parts = [];
        var buildingBlocks  = (document.getElementById('_building') || {}).value || '';
        var street = (document.getElementById('_street') || {}).value || '';
        var street_name    = (document.getElementById('_street_name') || {}).value || '';
        
        if (buildingBlocks.trim())  parts.push("Bldg. "+buildingBlocks.trim());
        if (street.trim()) parts.push("Street "+street.trim());
        if (street_name.trim())  parts.push("("+street_name.trim()+")");
       
        var address1 = document.getElementById('fields_address1');
        if (address1) address1.value = parts.join(', ');
    }
    ['_building', '_street', '_street_name'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', mergeAddress1);
    });
 
    function mergeAddress2() {
        var parts = [];
        
        var zone_area  = (document.getElementById('_area')  || {}).value || '';
        var unit    = (document.getElementById('_unit')    || {}).value || '';

        if (zone_area.trim())  parts.push("Zone "+zone_area.trim());
        if (unit.trim()) parts.push(unit.trim());
       
        var address2 = document.getElementById('fields_address2');
        if (address2) address2.value =  parts.join(', ');
    }

    ['_area', '_unit'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', mergeAddress2);
    });

    var state = document.getElementById('fields_state');
    var city = document.getElementById('fields_city');
    var zip = document.getElementById('fields_zip');
    if (state && city) {
        state.addEventListener('change', function () {
            city.value = state.value;
            zip.value = '.';
        });
    }
})();
</script>
 


