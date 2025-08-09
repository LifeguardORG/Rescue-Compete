<?php
namespace Staffel;

class StaffelInputController {
    private StaffelModel $model;
    public string $message = "";
    public array $modalData = [];
    public string $redirectUrl = "StaffelInputView.php";

    public function __construct(StaffelModel $model) {
        $this->model = $model;
    }

    public function handleRequest() {
        // Löschen einer Staffel
        if (isset($_POST['delete_Staffel'])) {
            $deleteNr = intval($_POST['delete_ID']);
            if ($this->model->delete($deleteNr)) {
                header("Location: " . $this->redirectUrl);
                exit;
            } else {
                $this->message = "Fehler beim Löschen der Staffel.";
            }
        }
        // Hinzufügen oder Aktualisieren einer Staffel
        if (isset($_POST['add_Staffel'])) {
            $name = trim($_POST['name']);

            // Prüfe, ob eine Staffel mit diesem Namen bereits existiert.
            if ($this->model->existsByName($name)) {
                $this->modalData = ['duplicate' => true];
            } else {
                $result = $this->model->create(['name' => $name]);
                if ($result !== false) {
                    header("Location: " . $this->redirectUrl);
                    exit;
                } else {
                    $this->message = 'Fehler beim Hinzufügen der Staffel.';
                }
            }
        }
    }
}
