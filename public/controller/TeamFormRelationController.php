<?php
namespace TeamForm;

use Mannschaft\MannschaftModel;
use QuestionForm\FormManagementModel;
use Station\StationModel;

class TeamFormRelationController
{
    private TeamFormRelationModel $teamFormRelationModel;
    private MannschaftModel $mannschaftModel;
    private FormManagementModel $formModel;
    private ?StationModel $stationModel;
    public string $message = "";
    public array $modalData = [];
    public string $redirectUrl = "TeamFormOverview.php";

    public function __construct(
        TeamFormRelationModel $teamFormRelationModel,
        MannschaftModel       $mannschaftModel,
        FormManagementModel   $formModel,
        StationModel          $stationModel = null
    ) {
        $this->teamFormRelationModel = $teamFormRelationModel;
        $this->mannschaftModel = $mannschaftModel;
        $this->formModel = $formModel;
        $this->stationModel = $stationModel;
    }

    public function handleRequest(): array
    {
        // Übersicht einer Mannschaft anzeigen
        if (isset($_GET['team_id'])) {
            return $this->getTeamDetails((int)$_GET['team_id']);
        }

        // Formular einer Mannschaft zuweisen
        if (isset($_POST['assign_form'])) {
            $teamId = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
            $formId = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;
            $sequence = isset($_POST['sequence']) ? (int)$_POST['sequence'] : 0;

            if ($teamId <= 0 || $formId <= 0) {
                $this->message = "Ungültige Team- oder Formular-ID.";
                return $this->getAllTeams();
            }

            if ($this->teamFormRelationModel->assignFormToTeam($teamId, $formId, $sequence)) {
                $this->message = "Formular erfolgreich zugewiesen.";
                return $this->getTeamDetails($teamId);
            } else {
                $this->message = "Fehler beim Zuweisen des Formulars.";
                return $this->getTeamDetails($teamId);
            }
        }

        // Alle Formulare einer Station für eine Mannschaft zuweisen
        if (isset($_POST['assign_station_forms'])) {
            $teamId = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
            $stationId = isset($_POST['station_id']) ? (int)$_POST['station_id'] : 0;

            if ($teamId <= 0 || $stationId <= 0) {
                $this->message = "Ungültige Team- oder Stations-ID.";
                return $this->getAllTeams();
            }

            $count = $this->teamFormRelationModel->assignAllFormsByStationToTeam($teamId, $stationId);
            if ($count > 0) {
                $this->message = "$count Formulare erfolgreich zugewiesen.";
            } else {
                $this->message = "Keine Formulare zugewiesen.";
            }
            return $this->getTeamDetails($teamId);
        }

        // Ein Formular allen Mannschaften zuweisen
        if (isset($_POST['assign_to_all_teams'])) {
            $formId = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;

            if ($formId <= 0) {
                $this->message = "Ungültige Formular-ID.";
                return $this->getAllTeams();
            }

            $count = $this->teamFormRelationModel->assignFormToAllTeams($formId);
            if ($count > 0) {
                $this->message = "Formular an $count Mannschaften zugewiesen.";
            } else {
                $this->message = "Formular konnte keiner Mannschaft zugewiesen werden.";
            }
            return $this->getAllTeams();
        }

        // Formular von einer Mannschaft entfernen
        if (isset($_POST['remove_form'])) {
            $teamId = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
            $formId = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;

            if ($teamId <= 0 || $formId <= 0) {
                $this->message = "Ungültige Team- oder Formular-ID.";
                return $this->getAllTeams();
            }

            if ($this->teamFormRelationModel->removeFormFromTeam($teamId, $formId)) {
                $this->message = "Formular erfolgreich entfernt.";
            } else {
                $this->message = "Fehler beim Entfernen des Formulars.";
            }
            return $this->getTeamDetails($teamId);
        }

        // Bearbeitungsstatus aktualisieren
        if (isset($_POST['update_completion'])) {
            $teamId = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
            $formId = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;
            $completed = isset($_POST['completed']) && $_POST['completed'] == 1;
            $points = isset($_POST['points']) ? (int)$_POST['points'] : 0;

            if ($teamId <= 0 || $formId <= 0) {
                $this->message = "Ungültige Team- oder Formular-ID.";
                return $this->getAllTeams();
            }

            if ($this->teamFormRelationModel->updateFormCompletion($teamId, $formId, $completed, $points)) {
                $this->message = "Status erfolgreich aktualisiert.";
            } else {
                $this->message = "Fehler beim Aktualisieren des Status.";
            }
            return $this->getTeamDetails($teamId);
        }

        // Standardansicht: Alle Mannschaften anzeigen
        return $this->getAllTeams();
    }

    /**
     * Holt alle Mannschaften mit ihren Formularstatistiken
     *
     * @return array Daten für die Anzeige in der View
     */
    public function getAllTeams(): array
    {
        $teams = $this->teamFormRelationModel->getAllTeamsWithFormsCount();
        $forms = $this->formModel->read(); // Alle verfügbaren Formulare

        return [
            'teams' => $teams,
            'forms' => $forms,
            'view' => 'teams_overview'
        ];
    }

    /**
     * Holt die Details einer Mannschaft mit zugewiesenen Formularen
     *
     * @param int $teamId ID der Mannschaft
     * @return array Daten für die Anzeige in der View
     */
    public function getTeamDetails(int $teamId): array
    {
        $team = $this->mannschaftModel->read($teamId);
        if (!$team) {
            $this->message = "Mannschaft nicht gefunden.";
            return $this->getAllTeams();
        }

        $assignedForms = $this->teamFormRelationModel->getFormsByTeam($teamId);

        // Hole alle verfügbaren Formulare
        $allForms = $this->formModel->read();

        // Filtere die Formulare, die noch nicht zugewiesen wurden
        $unassignedForms = [];
        foreach ($allForms as $form) {
            $isAssigned = false;
            foreach ($assignedForms as $assigned) {
                if ($assigned['form_ID'] == $form['ID']) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $unassignedForms[] = $form;
            }
        }

        // Hole alle Stationen für die Dropdown-Liste
        $stations = [];
        if ($this->stationModel) {
            $stations = $this->stationModel->read();
        }

        return [
            'team' => $team,
            'assigned_forms' => $assignedForms,
            'unassigned_forms' => $unassignedForms,
            'stations' => $stations,
            'view' => 'team_details'
        ];
    }
}