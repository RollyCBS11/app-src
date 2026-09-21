<?php
if (!isset($OfferApi)) { include_once("../integrated/setup.php"); }
use ddm\Backend\Translate\JsonTranslateCollector;
$collector_sh = new JsonTranslateCollector(
  targetLanguage:  $OfferApi->targetLanguage,
  pageName: "checkout-shipping"
);
$country="_mc";
?>


<div class="mb-3">
    <label class="p cart-input-label" for="address 1" label="Address Line 1"><?=$collector_sh->translate("address_label_".$country, "Address Line 1") ?></label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("street_placeholder_".$country, "Street and House Number"); ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="address 2" label="Building Name, Room Number, Company Dept."><?=$collector_sh->translate("address2_label_".$country, "Address Line 2"); ?></label>
    <div>
        <input id="fields_address2" name="address 2" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("address2_placeholder_".$country, "Address Line 2 (Optional)");?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="zip" label="Zip/Postal Code"><?= $collector_sh->translate("zip_label_".$country, "Postal Code"); ?></label>
    <div>
        <input id="fields_zip" name="zip" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("zip_placeholder_".$country, "Postal Code"); ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="city" label="Town/City"><?=$collector_sh->translate("town_city_label_".$country, "City"); ?></label>
    <div>
        <input id="fields_city" name="city" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("town_city_placehoder_".$country, "City"); ?>" required="required">
    </div>
</div>

<div class="mb-3" style="display:none;">
    <label class="p cart-input-label" for="state" label="State/Province"><?= $collector_sh->translate("state_label_".$country, "State"); ?></label>
    <div>
        <select  style="display:none;" id="fields_state" name="state" class="cart-input p" required="required"></select>
    </div>
</div>


<?php $collector_sh->saveTranslation(); ?>