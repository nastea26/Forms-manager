<?php
class Form {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createForm($userId, $title, $description) {
        $table = "forms";
        $columns = ["user_id", "title", "description", "is_active", "created_at"];
        $values = [$userId, $title, $description, 1, date('Y-m-d H:i:s')];
        return $this->db->insertInto($table, $columns, $values, True);
    }

    public function addQuestion($formId, $text, $type, $required) {
        $table = "questions";
        $columns = ["form_id", "question_text", "answer_type", "is_required", "created_at"];
        $values = [$formId, $text, $type, $required, date('Y-m-d H:i:s')];
        return $this->db->insertInto($table, $columns, $values, True);
    }

    public function addOption($questionId, $text) {
        $table = "choices";
        $columns = ["question_id", "option_text", "created_at"];
        $values = [$questionId, $text, date('Y-m-d H:i:s')];
        return $this->db->insertInto($table, $columns, $values, True);
    }

    public function getForm($formId) {
        $sql = "SELECT * FROM forms WHERE id = ?";
        return $this->db->searchQuery($sql, [$formId])->fetch_assoc();
    }

    public function getQuestions($formId) {
        $sql = "SELECT * FROM questions WHERE form_id = ?";
        return $this->db->searchQuery($sql, [$formId])->fetch_all(MYSQLI_ASSOC);
    }

    public function getFormDetails($formId) {
        // Fetch form metadata
        $sqlForm = "SELECT * FROM forms WHERE id = ?";
        $form = $this->db->searchQuery($sqlForm, [$formId])->fetch_assoc();
    
        // Fetch questions
        $sqlQuestions = "SELECT * FROM questions WHERE form_id = ?";
        $questions = $this->db->searchQuery($sqlQuestions, [$formId])->fetch_all(MYSQLI_ASSOC);
        // Fetch choices for each question
        foreach ($questions as &$question) {
            if (in_array($question['answer_type'], ['multiple_choice', 'checkbox'])) {
                $sqlChoices = "SELECT * FROM choices WHERE question_id = ?";
                $question['choices'] = $this->db->searchQuery($sqlChoices, [$question['id']])->fetch_all(MYSQLI_ASSOC);
            }
        }
    
        $form['questions'] = $questions;
        return $form;
    }

    public function getActiveForms() {
        $sql = "SELECT * FROM forms WHERE is_active = 1";
        return $this->db->searchQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function submitResponse($formId, $respondentId, $answers) {
        // Insert response metadata
        $table = "responses";
        $columns = ["form_id", "respondednt_id", "created_at"];
        $values = [$formId, $respondentId, date('Y-m-d H:i:s')];
    
        $responseId = $this->db->insertInto($table, $columns, $values, True);
        if (!isset($responseId)) {
            return false;
        }
    
        // Insert answers
        foreach ($answers as $questionId => $answer) {
            $table = "answers";
            $columns = ["response_id", "question_id", "answer_text", "created_at"];
    
            if (is_array($answer)) {
                // For array-type answers, insert each as a separate row
                foreach ($answer as $answerText) {
                    $values = [$responseId, $questionId, $answerText, date('Y-m-d H:i:s')];
                    if (!$this->db->insertInto($table, $columns, $values)) {
                        return false;
                    }
                }
            } else {
                // For single-value answers
                $values = [$responseId, $questionId, $answer, date('Y-m-d H:i:s')];
                if (!$this->db->insertInto($table, $columns, $values)) {
                    return false;
                }
            }
        }
    
        return true;
    }
}

