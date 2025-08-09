<?php



use Model\ResultConfigurationModel;

require_once '../model/ResultConfigurationModel.php';

/**
 * Controller zur Verwaltung der Ergebnis-Konfiguration.
 * Er stellt die Konfigurationsseite bereit und verarbeitet Updates.
 * Das Model baut intern die Datenbankverbindung auf (über DbConnection.php).
 */
class ResultConfigController {
    private ResultConfigurationModel $model;

    /**
     * Konstruktor: Instanziiert das ResultConfigurationModel.
     */
    public function __construct() {
        $this->model = new ResultConfigurationModel();
    }

    /**
     * Gibt die aktuelle Konfiguration zurück.
     * Falls keine Konfiguration vorliegt, werden Standardwerte verwendet.
     *
     * @return array Die aktuelle Konfiguration
     */
    public function getConfig(): array
    {
        $this->updateWeightsIfNeeded();
        $config = $this->model->getConfig();
        if (empty($config)) {
            // Standardkonfiguration
            $config = [
                'SHARE_SWIMMING' => 50,
                'SHARE_PARCOURS' => 50,
                'TOTAL_POINTS' => 12000,
                'DEDUCTION_INTERVAL_MS' => 100,
                'POINTS_DEDUCTION' => 1,
                'WEIGHTS' => []
            ];
            $this->model->updateConfig($config);
            $config = $this->model->getConfig();
        }
        return $config;
    }

    /**
     * Aktualisiert die Konfiguration anhand der POST-Daten und leitet anschließend zurück zur Edit-Seite.
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newData = $_POST;
            // Das Model arbeitet mit Arrays, daher erfolgt hier keine Umwandlung von WEIGHTS in JSON.
            $updateResult = $this->model->updateConfig($newData);
            header("Location: ../view/ResultConfiguration.php?status=success");
            exit;
        }
    }

    /**
     * Prüft, ob die in der Konfiguration gespeicherten WEIGHTS mit den aktuellen Stationsnamen übereinstimmen.
     * Falls es Unterschiede gibt, wird die WEIGHTS-Liste auf alle Stationen mit dem Standardwert 100 aktualisiert.
     */
    private function updateWeightsIfNeeded() {
        $newWeights = $this->model->updateWeights();
        $config = $this->model->getConfig();
        $currentWeights = isset($config['WEIGHTS']) && is_array($config['WEIGHTS']) ? $config['WEIGHTS'] : [];
        if ($currentWeights !== $newWeights) {
            $config['WEIGHTS'] = $newWeights;
            $this->model->updateConfig($config);
        }
    }
}

// Nur ausführen, wenn direkt aufgerufen (nicht bei include)
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    $action = $_GET['action'] ?? '';
    $controller = new ResultConfigController();

    if ($action === 'update') {
        $controller->update();
    } else {
        // Bei direktem Aufruf ohne Action zur View weiterleiten
        header("Location: ../view/ResultConfiguration.php");
        exit;
    }
}