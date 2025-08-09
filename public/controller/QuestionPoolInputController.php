<?php
namespace QuestionPool;

class QuestionPoolInputController
{
    private QuizPoolModel $model;
    public string $message = "";
    public array $modalData = [];
    public string $redirectUrl = "QuestionInputView.php";

    public function __construct(QuizPoolModel $model)
    {
        $this->model = $model;
    }

    public function handleRequest()
    {
        // Löschen eines Fragenpools
        if (isset($_POST['delete_pool'])) {
            $deleteId = intval($_POST['delete_id']);
            if ($this->model->delete($deleteId)) {
                header("Location: " . $this->redirectUrl);
                exit;
            } else {
                $this->message = "Fehler beim Löschen des Fragenpools.";
            }
        }

        // Hinzufügen oder Aktualisieren eines Fragenpools
        if (isset($_POST['add_pool'])) {
            $name = trim($_POST['name']);

            // Prüfen, ob der Name leer ist
            if (empty($name)) {
                $this->message = "Der Name des Fragenpools darf nicht leer sein.";
                return;
            }

            $confirmUpdate = isset($_POST['confirm_update']) && $_POST['confirm_update'] == "1";
            $providedDuplicateId = isset($_POST['duplicate_id']) ? intval($_POST['duplicate_id']) : null;

            // Prüfen, ob bereits ein Pool mit diesem Namen existiert
            $existingPool = $this->model->getPoolByName($name);

            if ($existingPool && !$confirmUpdate) {
                // Wenn ein Duplikat gefunden wurde und keine Bestätigung zum Überschreiben vorliegt
                $this->modalData = [
                    'message' => "Ein Fragenpool mit diesem Namen existiert bereits. Möchten Sie ihn aktualisieren?",
                    'duplicate_id' => $existingPool['ID'],
                    'name' => $name
                ];
            } else if ($confirmUpdate && $providedDuplicateId) {
                // Vereinfachtes Entry-Array, das nur den Namen enthält
                // Verwende keinen zweiten oder dritten Parameter, wenn er nicht benötigt wird
                if ($this->model->update($providedDuplicateId, ['name' => $name])) {
                    header("Location: " . $this->redirectUrl);
                    exit;
                } else {
                    $this->message = "Fehler beim Aktualisieren des Fragenpools.";
                }
            } else {
                // Vereinfachtes Entry-Array für das Erstellen eines neuen Pools
                // Nur die notwendigen Felder übergeben
                $newId = $this->model->create(['name' => $name]);
                if ($newId) {
                    header("Location: " . $this->redirectUrl);
                    exit;
                } else {
                    $this->message = "Fehler beim Erstellen des Fragenpools.";
                }
            }
        }
    }
}