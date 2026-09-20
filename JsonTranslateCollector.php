<?php
namespace app\src;
use DeepL\Translator;

class JsonCollector {
    // test comment
    // Product-name key convention: e.g. "product_name_pillow". These are stored/looked up
    // in a shared lang/<language>/product.json instead of the page-specific json file, so
    // the same product name text isn't duplicated (and re-translated) across every page.
    private const PRODUCT_KEY_MARKER = 'product_name';

    private static $data = []; // Private array to store collected
    private static $productData = [];
    // product_name keys whose English source text no longer matches the baseline saved in
    // lang/en/product.json. These get retranslated for every language, overwriting the
    // now-stale value, instead of merely filling in languages that are missing the key.
    private static $changedProductData = [];
    private static $storeTranslateLang =[];
    private static $storeProductTranslateLang =[];
    private static $existingTranslation =[];
    private static $existingProductTranslation =[];
    private $jsonPath = "";
    private $productJsonPath = "";
    private $translatedJson;
    private $translatedProductJson;
    private $sourceProductJson;
    private $deepLKey;
    private $targetLanguage;
    private $sourceLanguage ="en";
    private $subFolder ="";
    private $pageName="";
    private static array $calls = [];
    private $primaryJsonLoaded = false;
    private $primaryProductJsonLoaded = false;
    private int $progressTotal = 0;
    private int $progressDone = 0;


    public function __construct($targetLanguage="en",$pageName,$subFolder="") {
        $this->deepLKey = getenv('DEEPL_KEY');
        $this->makeFolder(BASEPATH.$subFolder."/lang/".$targetLanguage);
     
        $this->jsonPath = BASEPATH.$subFolder."/lang/".$targetLanguage."/".$pageName.".json";
        $this->productJsonPath = BASEPATH.$subFolder."/lang/".$targetLanguage."/product.json";
        $this->targetLanguage = $targetLanguage;
        $this->subFolder = $subFolder;
        $this->pageName = $pageName;
    }

    private function isProductKey(string $name): bool {
        return stripos($name, self::PRODUCT_KEY_MARKER) !== false;
    }

    private function makeFolder($folderPath){
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0755);
        }
    }

    public function translate(string $name, string $text, bool $checkDuplicate=true): string {
        $isProduct = $this->isProductKey($name);
        $text = trim($text);

        if ($isProduct) {
            if (!$this->primaryProductJsonLoaded) {
                $this->translatedProductJson = $this->getTranslatedJson($this->productJsonPath);
                $this->sourceProductJson = $this->getTranslatedJson($this->getProductSourceJsonPath());
                $this->primaryProductJsonLoaded = true;
            }
        } else {
            if (!$this->primaryJsonLoaded) {
                $this->translatedJson = $this->getTranslatedJson($this->jsonPath);
                $this->primaryJsonLoaded = true;
            }
        }

        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        //check duplicates only in local
        if(in_array($serverName, ['localhost', '127.0.0.1'])){
            if($checkDuplicate){
                if($this->checkDuplicateName($name)){
                    exit;
                }
            }
        }

        $searchValue = $this->searchTranslateKey($name, $isProduct);
        // Change detection only applies to product_name keys: they're shared across every
        // page via product.json, so a renamed product needs to propagate everywhere it's used.
        $sourceChanged = $isProduct && $this->hasProductSourceChanged($name, $text);

        if ($searchValue !== "" && !$sourceChanged) {
            return $searchValue;
        }

        if ($isProduct) {
            if ($sourceChanged) {
                self::$changedProductData[$name] = $text;
            } else {
                self::$productData[$name] = $text;
            }
        } else {
            self::$data[$name] = $text;
        }
        return $text;
    }

    private function getProductSourceJsonPath(): string {
        return BASEPATH.$this->subFolder."/lang/".$this->sourceLanguage."/product.json";
    }

    // True when the English text passed to translate() for a product_name key differs
    // from the English baseline last saved to lang/<sourceLanguage>/product.json.
    // A missing baseline (brand-new product/key) is not treated as a "change".
    private function hasProductSourceChanged(string $name, string $text): bool {
        $sourceJson = $this->sourceProductJson;
        if ($sourceJson === null || !array_key_exists($name, $sourceJson)) {
            return false;
        }
        return $sourceJson[$name] !== $text;
    }


    private function deepLTranslation(string $text, string $targetLanguage){
        // Map language codes to DeepL supported codes
        $deepLLanguageMap = [
            'zh-hans' => 'zh',     // Chinese Simplified
            'zh-hant' => 'zh',     // Chinese Traditional
            'pt-br' => 'pt-BR',    // Portuguese (Brazil)
            'pt-pt' => 'pt-PT',    // Portuguese (Portugal)
            'mx' => 'es',          // Spanish (Mexico) - DeepL has no MX-specific target
        ];

        // Get the correct DeepL language code
        $deeplLang = $deepLLanguageMap[$targetLanguage] ?? $targetLanguage;

        $translator = new Translator($this->deepLKey);
        $translateSettings=[
                    'tagHandling' => 'html',
                    'glossary'    => 'bac73f85-5b50-4681-b770-fa9b624e7165',
                    'ignoreTags' => "nt"
                ];

        $noGlossaryLangs = ['tl','da','pl','hu','cs','it','fi','es','fr','de','nl','sv','no','ro','sk','sl','et','lv','lt','vi','id','ca','ar', 'ro'];
        if(in_array(strtolower($targetLanguage), $noGlossaryLangs)){
            unset($translateSettings['glossary']);
        }

        try {
            $result = $translator->translateText(
                $text,
                $this->sourceLanguage,
                $deeplLang,
                $translateSettings
            );
            return $result->text;
        } catch (\Throwable $e) {
            error_log("DeepL translation failed for target language '{$targetLanguage}' ({$deeplLang}): " . $e->getMessage());

            // Retry once without the glossary, in case the pair isn't covered by it.
            if (isset($translateSettings['glossary'])) {
                unset($translateSettings['glossary']);
                try {
                    $result = $translator->translateText(
                        $text,
                        $this->sourceLanguage,
                        $deeplLang,
                        $translateSettings
                    );
                    return $result->text;
                } catch (\Throwable $e2) {
                    error_log("DeepL retry without glossary also failed for target language '{$targetLanguage}' ({$deeplLang}): " . $e2->getMessage());
                }
            }

            // Fall back to the untranslated source text so one bad language pair
            // doesn't abort translation/save for every other language.
            return $text;
        }
    }

    private function checkDuplicateName($name) {       

        if (!isset(self::$calls[$name])) {
            self::$calls[$name] = 0;
        }

        self::$calls[$name]++;
        if (self::$calls[$name] > 1) {
            echo "<script>".
                 "alert('Translation key {$name} was assigned more than once! (".self::$calls[$name]." times).It should be unique');".
                 "</script>";

            return true;
        } else {
            return false;
        }
    }

    private function getTranslatedJson($filePath){
        $jsonContents = file_get_contents($filePath);
        if ($jsonContents === false) {
            return null;
        }
        // Decode JSON to an associative array
        $data = json_decode($jsonContents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }else{
            return $data;
        }
    }

    private function searchTranslateKey(string $searchKey, bool $isProduct = false){
        $json = $isProduct ? $this->translatedProductJson : $this->translatedJson;
        if($json==null){
            return "";
        }
        if (array_key_exists($searchKey, $json)) {
            return $json[$searchKey];
        }else {
            return "";
        }
    }

    public function translateDefinedLanguages(string $key, string $value, bool $isProduct = false, bool $forceUpdate = false){

        $directory = BASEPATH.$this->subFolder."/lang"; // Replace with your directory path
        $baseName = $isProduct ? "product" : $this->pageName;

        if (is_dir($directory)) {
            // Get all files and directories in the specified path
            $items = scandir($directory);

            foreach ($items as $targetLanguage) {
                // Skip the special "." (current directory) and ".." (parent directory) entries
                if ($targetLanguage !== '.' && $targetLanguage !== '..') {
                    $path = $directory . DIRECTORY_SEPARATOR . $targetLanguage;
                    // Check if the item is a directory
                    if (is_dir($path) && $targetLanguage !="system") {

                          $filePath = BASEPATH.$this->subFolder."/lang/".$targetLanguage."/".$baseName.".json";
                          if(file_exists($filePath)){
                                $existing = $this->getTranslatedJson($filePath);
                                if ($isProduct) {
                                    self::$existingProductTranslation[$targetLanguage] = $existing;
                                    $this->translatedProductJson = $existing;
                                } else {
                                    self::$existingTranslation[$targetLanguage] = $existing;
                                    $this->translatedJson = $existing;
                                }

                                // forceUpdate re-translates even languages that already have a
                                // (now stale) translation for $key, so a renamed product_name
                                // propagates everywhere, not just into languages missing the key.
                                if($forceUpdate || $this->searchTranslateKey($key, $isProduct)==""){
                                    $translated = ($targetLanguage!=$this->sourceLanguage)
                                        ? $this->deepLTranslation($value,$targetLanguage)
                                        : $value;
                                    if ($isProduct) {
                                        self::$storeProductTranslateLang[$targetLanguage][$key]=$translated;
                                    } else {
                                        self::$storeTranslateLang[$targetLanguage][$key]=$translated;
                                    }
                                }
                                $this->updateProgress();
                          }

                    }
                }
            }
        }
    }


    public function translateDefinedLanguagesNewPage(string $key, string $value, bool $isProduct = false){

        $directory = BASEPATH.$this->subFolder."/lang"; // Replace with your directory path
        $baseName = $isProduct ? "product" : $this->pageName;

        if (is_dir($directory)) {
            // Get all files and directories in the specified path
            $items = scandir($directory);

            foreach ($items as $targetLanguage) {
                // Skip the special "." (current directory) and ".." (parent directory) entries
                if ($targetLanguage !== '.' && $targetLanguage !== '..') {
                    $path = $directory . DIRECTORY_SEPARATOR . $targetLanguage;
                    // Check if the item is a directory
                    if (is_dir($path) && $targetLanguage !="system") {
                        $filePath = BASEPATH.$this->subFolder."/lang/".$targetLanguage."/".$baseName.".json";
                        if(!file_exists($filePath)){
                            $translated = ($targetLanguage!=$this->sourceLanguage)
                                ? $this->deepLTranslation($value,$targetLanguage)
                                : $value;
                            if ($isProduct) {
                                self::$storeProductTranslateLang[$targetLanguage][$key]=$translated;
                            } else {
                                self::$storeTranslateLang[$targetLanguage][$key]=$translated;
                            }
                            $this->updateProgress();
                        }
                    }
                }
            }
        }
    }

    private function countTargetLanguages(bool $isProduct = false): int {
        $directory = BASEPATH.$this->subFolder."/lang";
        if (!is_dir($directory)) {
            return 0;
        }

        // deepLExec() picks the same branch (existing page vs. new page) for every
        // key in a group, since it only depends on the group's json path, which
        // doesn't change mid-run.
        $baseName = $isProduct ? "product" : $this->pageName;
        $usesExistingPageBranch = file_exists($isProduct ? $this->productJsonPath : $this->jsonPath);

        $count = 0;
        foreach (scandir($directory) as $targetLanguage) {
            if ($targetLanguage === '.' || $targetLanguage === '..' || $targetLanguage === 'system') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $targetLanguage;
            if (!is_dir($path)) {
                continue;
            }
            $filePath = BASEPATH.$this->subFolder."/lang/".$targetLanguage."/".$baseName.".json";
            $fileExists = file_exists($filePath);
            if ($usesExistingPageBranch === $fileExists) {
                $count++;
            }
        }
        return $count;
    }

    // Counts language dirs that already have product.json — i.e. the dirs
    // translateDefinedLanguages() will actually touch when forceUpdate retranslates a
    // changed product_name key. Used to size the progress bar for that case.
    private function countProductLanguageDirsWithFile(): int {
        $directory = BASEPATH.$this->subFolder."/lang";
        if (!is_dir($directory)) {
            return 0;
        }

        $count = 0;
        foreach (scandir($directory) as $targetLanguage) {
            if ($targetLanguage === '.' || $targetLanguage === '..' || $targetLanguage === 'system') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $targetLanguage;
            if (!is_dir($path)) {
                continue;
            }
            $filePath = BASEPATH.$this->subFolder."/lang/".$targetLanguage."/product.json";
            if (file_exists($filePath)) {
                $count++;
            }
        }
        return $count;
    }

    private function startProgressOutput(): void {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        // The page HTML above was likely accumulated in an output buffer (for
        // minifying/caching); flush it now so the browser has something to paint,
        // then stream progress updates as each translation call completes.
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
        if (function_exists('ob_implicit_flush')) {
            ob_implicit_flush(true);
        }
        if (!headers_sent()) {
            header('X-Accel-Buffering: no');
        }

        echo '<div id="jc-progress-overlay" style="position:fixed;top:0;left:0;right:0;background:#111;color:#fff;'
            . 'font:13px/1.4 -apple-system,Segoe UI,sans-serif;padding:10px 16px;z-index:999999;'
            . 'box-shadow:0 -2px 10px rgba(0,0,0,.35);">'
            . '<div style="display:flex;justify-content:space-between;margin-bottom:6px;">'
            . '<span id="jc-progress-status">Translating new content&hellip;</span>'
            . '<span id="jc-progress-label">0%</span>'
            . '</div>'
            . '<div style="background:#333;border-radius:4px;overflow:hidden;height:8px;">'
            . '<div id="jc-progress-bar" style="height:100%;width:0%;background:#4caf50;transition:width .2s;"></div>'
            . '</div>'
            . '</div>' . PHP_EOL;
        flush();
    }

    private function updateProgress(): void {
        if ($this->progressTotal <= 0) {
            return;
        }
        $this->progressDone++;
        $percent = (int) min(100, round(($this->progressDone / $this->progressTotal) * 100));
        echo '<script>(function(){'
            . "var b=document.getElementById('jc-progress-bar'),l=document.getElementById('jc-progress-label');"
            . "if(b){b.style.width='{$percent}%';}"
            . "if(l){l.textContent='{$percent}% ({$this->progressDone}/{$this->progressTotal})';}"
            . '})();</script>' . PHP_EOL;
        flush();
    }

    private function finishProgressOutput(string $redirectUrl): void {
        $redirectJson = json_encode($redirectUrl);
        echo '<script>(function(){'
            . "var s=document.getElementById('jc-progress-status'),b=document.getElementById('jc-progress-bar'),l=document.getElementById('jc-progress-label');"
            . "if(s){s.textContent='Done. Reloading\\u2026';}"
            . "if(b){b.style.width='100%';}"
            . "if(l){l.textContent='100%';}"
            . "window.location.href={$redirectJson};"
            . '})();</script>' . PHP_EOL;
        flush();
    }

    private function deepLExec(){
        foreach (self::$data as $key => $value) {
            if(file_exists($this->jsonPath)){
                $this->translateDefinedLanguages($key, $value);
            }else{
                $this->translateDefinedLanguagesNewPage($key, $value);
            }
        }
        foreach (self::$productData as $key => $value) {
            if(file_exists($this->productJsonPath)){
                $this->translateDefinedLanguages($key, $value, true);
            }else{
                $this->translateDefinedLanguagesNewPage($key, $value, true);
            }
        }
        foreach (self::$changedProductData as $key => $value) {
            $this->translateDefinedLanguages($key, $value, true, true);
        }
    }

    private function writeGroupTranslations(string $baseName, array $existingTranslation, array $storeTranslateLang): void {
        foreach ($storeTranslateLang as $languageCode => $translations) {
            $filePath = BASEPATH.$this->subFolder."/lang/".$languageCode."/".$baseName.".json";
            if(!empty($existingTranslation[$languageCode])){
                 $merged = array_merge($existingTranslation[$languageCode], $translations);
                 $jsonData = json_encode($merged, JSON_PRETTY_PRINT);
            }else{
                $jsonData = json_encode($translations, JSON_PRETTY_PRINT);
            }
            if (file_put_contents($filePath, $jsonData) === false) {
                trigger_error("Failed to write to file: $filePath", E_USER_WARNING);
            }
        }
    }

    public function saveTranslation(): bool {

        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        //don't save if there's nothing new or changed to translate
        $hasWork = count(self::$data) > 0 || count(self::$productData) > 0 || count(self::$changedProductData) > 0;
        if(!$hasWork || !in_array($serverName, ['localhost', '127.0.0.1'])){
            return false;
        }

        $this->progressTotal = (count(self::$data) * $this->countTargetLanguages())
            + (count(self::$productData) * $this->countTargetLanguages(true))
            + (count(self::$changedProductData) * $this->countProductLanguageDirsWithFile());
        $this->progressDone = 0;
        $this->startProgressOutput();

        $this->deepLExec();

        $this->writeGroupTranslations($this->pageName, self::$existingTranslation, self::$storeTranslateLang);
        $this->writeGroupTranslations("product", self::$existingProductTranslation, self::$storeProductTranslateLang);

        // Check for JSON encoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON encoding error: " . json_last_error_msg());
            return false;
        }

        $currentPage = $_SERVER['REQUEST_URI'];
        // Progress output above already sent real content to the browser, so a
        // Location header can no longer be used — redirect via JS instead.
        $this->finishProgressOutput($currentPage);

        return true;
    }
}

?>