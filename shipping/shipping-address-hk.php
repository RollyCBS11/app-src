<?php
if (!isset($OfferApi)) { include_once("../../integrated/setup.php"); }
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
$collector_sh = new JsonCollector(
    targetLanguage: $OfferApi->targetLanguage,
    pageName: "checkout-shipping"
);
?>


<div class="mb-3">
    <label class="p cart-input-label" for="city" label="Town/City"><?= $collector_sh->translate("town_city_hk", "District") ?></label>
    <div>
        <input id="fields_city" name="city" class="cart-input p" value="" type="text" placeholder="" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="address 1" label="Street, house and apartment number"><?= $collector_sh->translate("p_address_1_hk", "Unit/Floor/Bldg") ?></label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" value="" type="text" placeholder="" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="address 2" label="Street, house and apartment number"><?= $collector_sh->translate("p_address_2_hk", "Street/Road") ?></label>
    <div>
        <input id="fields_address2" name="address 2" class="cart-input p" value="" type="text" placeholder="" required="required">
    </div>
</div>

<div class="mb-3 ">
    <label class="p cart-input-label" for="fields_houseno"><?= $collector_sh->translate("house_building_no_hk", "House/Building number"); ?></label>
    <div>
        <input id="fields_houseno" name="fields_houseno" class="cart-input p" value="" type="text" placeholder="" required="required">
    </div>
</div>
<div class="row align-items-start d-none" style="display:none;">
    <div class="col-sm-12 pr-sm-2 ">
        <div class="row mb-n3 align-items-start">
            <div class="col-sm-6 pr-sm-2">
                <div class="mb-3">
                    <label class="p cart-input-label" for="state" label="State/Province"><?= $collector_sh->translate("state_hk", "State/Province"); ?></label>
                    <div>
                        <select id="fields_state" name="state" class="cart-input p" required="required">
                            <option value=""><?= $collector_sh->translate("p_state_hk", "Select State / Province"); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="mb-3">
                    <label class="p cart-input-label" for="zip" label="Zip/Postal Code"><?= $collector_sh->translate("zip_hk", "Zip / Postal Code"); ?></label>
                    <div>
                        <input id="fields_zip" name="zip" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_zip_hk", "Zip/Postal Code"); ?>" required="required">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $collector_sh->saveTranslation(); ?>