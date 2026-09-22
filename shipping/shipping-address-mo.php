<?php
if (!isset($OfferApi)) { include_once("../../integrated/setup.php"); }
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
$collector_sh = new JsonCollector(
    targetLanguage: $OfferApi->targetLanguage,
    pageName: "checkout-shipping"
);
?>


<div class="mb-3">
    <label class="p cart-input-label" for="address 1" label="Street, house and apartment number"><?= $collector_sh->translate("p_address_1_mo", "Street and building",false) ?></label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_address_1_mo", "Street and building",false); ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="address 2" label="Street, house and apartment number"><?= $collector_sh->translate("p_address_2_mo", "Floor / Block/ Flat",false); ?></label>
    <div>
        <input id="fields_address2" name="address 2" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_address_2_mo", "Floor / Block/ Flat",false); ?>" required="required">
    </div>
</div>

<div class="mb-3 d-none">
    <label class="p cart-input-label" for="district" label="District"><?= $collector_sh->translate("district_mo", "District"); ?></label>
    <div>
        <input id="fields_state" name="district" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("district_mo_pl", "District"); ?>" required="required">
    </div>
</div>

<div class="mb-3 d-none">
    <label class="p cart-input-label" for="zip" label="Zip/Postal Code"><?= $collector_sh->translate("zip_mo", "Postal Code"); ?></label>
    <div>
        <input id="fields_zip" name="zip" class="cart-input p" value="999078" type="text" placeholder="<?= $collector_sh->translate("p_zip_mo", "Postal Code"); ?>" required="required">
    </div>
</div>

<div class="mb-3 d-none">
    <label class="p cart-input-label" for="city" label="Town/City"><?= $collector_sh->translate("town_city_mo", "City"); ?></label>
    <div>
        <input id="fields_city" name="city" class="cart-input p" value="Macao" type="text" placeholder="<?= $collector_sh->translate("p_town_city_mo", "City"); ?>" required="required">
    </div>
</div>


<?php $collector_sh->saveTranslation(); ?>
