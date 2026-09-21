<?php
if (!isset($OfferApi)) { include_once("../integrated/setup.php"); }
use ddm\Backend\Translate\JsonTranslateCollector;
$collector_sh = new JsonTranslateCollector(
  targetLanguage: $OfferApi->targetLanguage,
  pageName: "checkout-shipping"
);

$country="it";
?>


<div class="mb-3">
    <label class="p cart-input-label" for="address 1" label="Street, house and apartment number"><?=$collector_sh->translate("address_1_".$country, "Address 1") ?></label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("p_address_1_".$country, "Street Address"); ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="address 2" label="Street, house and apartment number"><?=$collector_sh->translate("address_2_".$country, "Address 2 (Optional)"); ?></label>
    <div>
        <input id="fields_address2" name="address 2" class="cart-input p" value="" type="text" placeholder="<?=$collector_sh->translate("p_address_2_".$country, "Apt, suite, unit, etc.");?>" required="required">
    </div>
</div>
<div class="mb-3">
    <label class="p cart-input-label" for="zip" label="Zip/Postal Code"><?= $collector_sh->translate("zip_".$country, "Zip / Postal Code"); ?></label>
    <div>
        <input id="fields_zip" name="zip" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_zip_".$country, "Zip/Postal Code"); ?>" required="required">
    </div>
</div>


<div class="row align-items-start">
    <div class="col-sm-12">
        <div class="row mb-n3 align-items-start ">
            <div class="col-sm-6 pr-sm-2  ">
                <div class="mb-3 ">
                    <label class="p cart-input-label" for="city" label="Town/City"><?=$collector_sh->translate("town_city_".$country, "Town/City"); ?></label>
                    <div>
                        <input id="fields_city" name="city" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_town_city_".$country, "Town/City"); ?>" required="required">
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="mb-3">
                    <label class="p cart-input-label" for="state" label="State/Province"><?= $collector_sh->translate("province_".$country, "Province"); ?></label>
                    <div>
                        <select id="fields_state" name="state" class="cart-input p">
                            <option value=""><?= $collector_sh->translate("p_province_".$country, "Select Province"); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $collector_sh->saveTranslation(); ?>