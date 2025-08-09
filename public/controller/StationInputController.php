<?php
namespace Station;

class StationInputController {
    private StationModel $model;
    public string $message = "";
    public array $modalData = [];
    public string $redirectUrl = "StationInputView.php";

    public function __construct(StationModel $model) {
        $this->model = $model;
    }

    public function handleRequest() {
        // Löschen einer Station
        if (isset($_POST['delete_station'])) {
            $id = intval($_POST['delete_ID']);
            if ($this->model->delete($id)) {
                header("Location: " . $this->redirectUrl);
                exit;
            } else {
                $this->message = "Fehler beim Löschen der Station.";
            }
        }

        // Hinzufügen oder Aktualisieren einer Station
        if (isset($_POST['add_station'])) {
            $name = trim($_POST['name']);
            $nr = intval(trim($_POST['Nr']));

            // Prüfe auf doppelten Namen
            $existingNameId = $this->model->existsByName($name);
            if ($existingNameId) {
                $this->modalData = ['duplicateName' => true];
                return;
            }

            // Prüfe auf doppelte Stationsnummer
            $existingNrId = $this->model->existsByNr($nr);
            if ($existingNrId) {
                $this->modalData = ['duplicateNumber' => true, 'number' => $nr];
                return;
            }

            // Wenn keine Duplikate gefunden, erstelle neue Station
            $result = $this->model->create(['name' => $name, 'Nr' => $nr]);
            if ($result) {
                header("Location: " . $this->redirectUrl);
                exit;
            } else {
                $this->message = 'Fehler beim Erstellen der Station.';
            }
        }
    }
}