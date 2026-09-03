<?php
final class FetchData
{
    private static function FetchMealDB($type)
    {
        try {
            switch ($type) {
                case 'categories':
                    $url = "https://www.themealdb.com/api/json/v1/1/categories.php";
                    $dataKey = 'categories';
                    break;
                case 'ingredients':
                    $url = "https://www.themealdb.com/api/json/v1/1/list.php?i=list";
                    $dataKey = 'meals';
                    break;
                default:
                    throw new ImportException("Hibás adat típus.");
            }

            Logger::Log("Making request to: $url", logLvl::Info);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new ImportException("Nem sikerült lekérni a kiválasztott tartalmat.");
            }

            curl_close($ch);

            $data = json_decode($json, true);

            if (empty($data[$dataKey])) {
                throw new ImportException("A dataKey hibás/üres ($dataKey).");
            }

            $returnData = [];
            $dataKey === 'categories' ? $returnData = array_column($data[$dataKey], 'strCategory') : $returnData = array_column($data[$dataKey], 'strIngredient');
            $returnData = array_filter($returnData, fn ($val) => ! empty($val) && trim($val) !== '');

            if (empty($returnData)) {
                throw new ImportException("Nem található valid név $type type alapján.");
            }

            return $returnData;

        } catch (Exception $e) {
            throw new ImportException("Hiba a MealDB adatok lekérése közben: ".$e->getMessage());
        }
    }

    private static function TranslateData(array $data)
    {
        try {
            global $cfg;

            if (empty($cfg["googleAPIKey"])) {
                Logger::Log("Google API key not configured", logLvl::Warning);
                return false;
            }

            $apiKey = $cfg["googleAPIKey"];
            $url = "https://translation.googleapis.com/language/translate/v2?key=$apiKey";

            if (is_array($data) && ! empty($data)) {
                $queryParts = ['target=hu', 'format=text'];
                foreach ($data as $item) {
                    $queryParts[] = 'q='.urlencode($item);
                }
                $postBody = implode('&', $queryParts);


                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    throw new ImportException("Nem sikerült lefordítani a megadott tartalmat.");
                }
                curl_close($ch);

                $result = json_decode($response, true);

                return array_map(function ($item) {
                    return $item['translatedText'];
                }, $result['data']['translations']);
            }
            throw new ImportException("Hibás adatok.");


        } catch (Exception $e) {
            Logger::Log("Fordtó hiba: ".$e->getMessage(), logLvl::Error);
            return false;
        }
    }

    public static function FetchByType(string $type)
    {
        try {
            $data = array_values(self::FetchMealDB($type));
            $translations = [];
            
            foreach (array_chunk($data, 100) as $chunk) {
                $result = self::translateData($chunk);
                $translations = array_merge($translations, $result ?: $chunk);
            }
            
            match ($type) {
                'categories' => CategoryQueryService::BatchImport($translations),
                'ingredients' => IngredientQueryService::BatchImport($translations)
            };
            
        } catch (Exception $e) {
            throw new FetchException("A $type lekérése/importálása hibába ütközött: ".$e->getMessage());
        }
    }
}
