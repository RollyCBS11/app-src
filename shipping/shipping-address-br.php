<?php
if (!isset($OfferApi)) { include_once("../integrated/setup.php"); }
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
$collector_sh = new JsonCollector(
    targetLanguage: $OfferApi->targetLanguage,
    pageName: "checkout-shipping"
);

$country = "br";
?>


<div class="mb-3">
    <label class="p cart-input-label" for="address 1" label="Street, house and apartment number"><?= $collector_sh->translate("address_1".$country, "Address 1") ?></label>
    <div>
        <input id="fields_address1" name="address 1" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_address_1".$country, "Address 1") ?>" required="required">
    </div>
</div>

<div class="mb-3 d-none">
    <label class="p cart-input-label" for="address 2" label="Street, house and apartment number"><?= $collector_sh->translate("address_2".$country, "Address 2") ?></label>
    <div>
        <input id="fields_address2" name="address 2" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_address_2".$country, "Address 2") ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="fields_houseno"><?= $collector_sh->translate("house_building_no__".$country, "House/Building number"); ?></label>
    <div>
        <input id="fields_houseno" name="fields_houseno" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("house_building_no_1".$country, "House/Building number"); ?>" required="required">
    </div>
</div>

<div class="mb-3">
    <label class="p cart-input-label" for="fields_bairro"><?= $collector_sh->translate("fields_bairro".$country, "Neighborhood"); ?></label>
    <div>
        <input id="fields_bairro" name="fields_bairro" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("fields_bairro2".$country, "Neighborhood"); ?>" required="required">
    </div>
</div>


<div class="mb-3">
    <label class="p cart-input-label" for="city" label="Town/City"><?= $collector_sh->translate("town_city".$country, "Town/City") ?></label>
    <div>
        <input id="fields_city" name="city" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_town_city".$country, "Town/City") ?>" required="required">
    </div>
</div>


<div class="row align-items-start">
    <div class="col-sm-12">
        <div class="row mb-n3 align-items-start">
            <div class="col-sm-6 pr-sm-2">
                <div class="mb-3">
                    <label class="p cart-input-label" for="state" label="State/Province"><?= $collector_sh->translate("state".$country, "State/Province"); ?></label>
                    <div>
                        <select id="fields_state" name="state" class="cart-input p" required="required">
                            <option value=""><?= $collector_sh->translate("p_state".$country, "Select State / Province"); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="mb-3">
                    <label class="p cart-input-label" for="zip" label="Zip/Postal Code"><?= $collector_sh->translate("zip".$country, "Zip / Postal Code"); ?></label>
                    <div>
                        <input id="fields_zip" name="zip" class="cart-input p" value="" type="text" placeholder="<?= $collector_sh->translate("p_zip".$country, "Zip/Postal Code"); ?>" required="required">
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3 mt-3">
            <label class="p cart-input-label" for="fields_cpf">CPF
                <button type="button" id="pcpf-info-btn" aria-label="CPF info" class="tw-ml-1 tw-inline-flex tw-items-center tw-justify-center tw-w-4 tw-h-4 tw-rounded-full tw-bg-gray-400 tw-text-white tw-text-[10px] tw-font-bold tw-leading-none tw-cursor-pointer tw-border-0 tw-align-middle">i</button>
            </label>
            <div>
                <input id="fields_cpf" name="fields_cpf" class="cart-input p" value="" type="text" placeholder="000.000,000-00" required="required">
            </div>
            <div id="cpf-helper" class="tw-hidden tw-mt-2 tw-p-3 tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-lg tw-text-sm tw-text-gray-700 tw-leading-relaxed">
                <p class="tw-font-semibold tw-mb-1">CPF (Cadastro de Pessoas Físicas)</p>
                <p class="tw-mb-1">Este é o número de 11 dígitos obrigatório pela Alfândega brasileira para todas as importações e compras internacionais.</p>
                <p class="tw-mb-2">Você pode obter ou consultar o seu rapidamente no site oficial da Receita Federal.</p>
                <a href="https://servicos.receita.fazenda.gov.br/Servicos/CPF/ConsultaSituacao/ConsultaPublica.asp" target="_blank" rel="noopener noreferrer" class="tw-font-semibold tw-text-blue-600 tw-underline">Obter CPF aqui &rarr;</a>
                <p class="tw-mt-2">Basta informar seu CPF no campo Tax ID / CPF durante o checkout.</p>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var input = document.getElementById('fields_cpf');
    var helper = document.getElementById('cpf-helper');
    var btn = document.getElementById('pcpf-info-btn');

    function show() { helper.classList.remove('tw-hidden'); }
    function hide() { if (document.activeElement !== input) helper.classList.add('tw-hidden'); }

    input.addEventListener('focus', show);
    input.addEventListener('blur', function () { setTimeout(hide, 150); });
    btn.addEventListener('click', function () { helper.classList.toggle('tw-hidden'); });
})();
</script>
<?php $collector_sh->saveTranslation(); ?>