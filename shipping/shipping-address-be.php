<?php
if (!isset($OfferApi)) { include_once("../integrated/setup.php"); }
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
$collector_sh = new JsonCollector(
  targetLanguage: $OfferApi->targetLanguage,
  pageName: "checkout-shipping"
);
$countryKey = 'be';
?>

<div class="mb-3">
    <label class="p cart-input-label" for="address 1" label="Address Line 1"><?=$collector_sh->translate("address_label_".$countryKey, "Address Line 1") ?></label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("street_placeholder_".$countryKey, "Street and House Number"); ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="address 2"><?=$collector_sh->translate("address2_label_".$countryKey, "Address Line 2 (Optional)"); ?></label>
    <div>
        <input id="fields_address2" name="address 2" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("address2_placeholder_".$countryKey, "Flat, Floor");?>">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="zip"><?= $collector_sh->translate("zip_label_".$countryKey, "Postal Code"); ?></label>
    <div>
        <input id="fields_zip" name="zip" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("zip_placeholder_".$countryKey, "Postal Code"); ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="city"><?=$collector_sh->translate("town_city_label_".$countryKey, "City/Commune"); ?></label>
    <div>
        <input id="fields_city" name="city" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("town_city_placehoder_".$countryKey, "City/Commune"); ?>" required="required">
    </div>
</div>

<div class="mb-3" style="display:none;">
    <label class="p cart-input-label" for="state"><?= $collector_sh->translate("state_label_".$countryKey, "State"); ?></label>
    <div>
        <select style="display:none;" id="fields_state" name="state" class="cart-input p" required="required"></select>
    </div>
</div>

<?php $collector_sh->saveTranslation(); ?>
