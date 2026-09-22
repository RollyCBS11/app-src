<?php
if (!isset($OfferApi)) { include_once("../integrated/setup.php"); }
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
$collector_sh = new JsonCollector(
  targetLanguage: $OfferApi->targetLanguage,
  pageName: "checkout-shipping"
);

$country="_mx";
?>
<?php if($OfferApi->targetLanguage=="zh-hant" || $OfferApi->targetLanguage=="zh-hans"){ ?>
            <div class="tw-mb-2 tw-flex tw-gap-1 tw-text-[#4D4D4D]" style="font-size:0.75em; padding: 7px;border: 1px solid #f6ca79;background: #fef6e9;line-height: 1.3;" >
                <span>&#x2139;</span><span><?= $collector_sh->translate("type_english_us", "Please fill in the following shipping information in English.") ?></span>
            </div>
<?php } ?>

<div class="mb-3">
    <label class="p cart-input-label" for="address 1" label="address 1"><?=$collector_sh->translate("address_1".$country, "Street") ?></label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("p_address_1".$country, "Street"); ?>" required="required">
    </div>
</div>
<div class="mb-3">
    <label class="p cart-input-label" for="fields_houseno"><?=$collector_sh->translate("Exterior_number ".$country, "Exterior number ") ?></label>
    <div>
        <input id="fields_houseno" name="fields_houseno" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("p_exterior_number ".$country, "Building / House no.") ?>" required="required" data-gtm-form-interact-field-id="1">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="fields_apt_unit"><?=$collector_sh->translate("interior_number ".$country, "Interior number ") ?></label>
    <div>
        <input id="fields_apt_unit" name="fields_apt_unit" class="cart-input p" value="" type="text" optional placeholder="<?=$collector_sh->translate("p_interior_number ".$country, "Apt / Unit") ?>" required="required" data-gtm-form-interact-field-id="1">
    </div>
</div>

<div class="mb-3 d-none">
    <label class="p cart-input-label" for="address 2" label="address 2"><?=$collector_sh->translate("address_2".$country, "Address 2"); ?></label>
    <div>
        <input id="fields_address2" name="address 2" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("p_address_2".$country, "Address 2");?>" required="required">
    </div>
</div>
<div class="mb-3">
    <label class="p cart-input-label" for="neighborhood" label="neighborhood"><?=$collector_sh->translate("neighborhood".$country, "Neighborhood (colonia)"); ?></label>
    <div>
        <input id="fields_neighborhood" name="neighborhood" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("p_neighborhood".$country, "Neighborhood (colonia)");?>" required="required">
    </div>
</div>

<div class="mb-3 ">
    <label class="p cart-input-label" for="city" label="Town/City"><?=$collector_sh->translate("town_city".$country, "Municipality / borough"); ?></label>
    <div>
        <input id="fields_city" name="city" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_town_city".$country, "Municipality / borough"); ?>" required="required">
    </div>
</div>

<div class="row align-items-start mb-3">
    <div class="col-sm-12">
        <div class="row mb-n3 align-items-start ">
            <div class="col-sm-6 pr-sm-2  ">
                <div class="mb-3">
                    <label class="p cart-input-label" for="state" label="State/Province"><?= $collector_sh->translate("state".$country, "State"); ?></label>
                    <div>
                        <select id="fields_state" name="state" class="cart-input p" required="required">
                            <option value=""><?= $collector_sh->translate("p_state".$country, "Select State"); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="mb-3">
                    <label class="p cart-input-label" for="zip" label="Zip/Postal Code"><?= $collector_sh->translate("zip".$country, "Postal code"); ?></label>
                    <div>
                        <input id="fields_zip" name="zip" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_zip".$country, "Postal code"); ?>" required="required">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="mb-3">
    <label class="p cart-input-label" for="tax_id" label="tax_id"><?=$collector_sh->translate("tax_id".$country, "RFC or CURP"); ?></label>
    <div>
        <input id="fields_tax_id" name="tax_id" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_tax_id".$country, "ABCD850115XYZ or ABCD850115HDFXXXX01"); ?>" required="required">
    </div>
</div>
<?php $collector_sh->saveTranslation(); ?>