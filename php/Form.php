<?php
class Form
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createForm($userId, $title, $description)
    {
        $table = "forms";
        $columns = ["user_id", "title", "description", "is_active", "created_at"];
        $values = [$userId, $title, $description, 1, date('Y-m-d H:i:s')];
        return $this->db->insertInto($table, $columns, $values, True);
    }

    public function addQuestion($formId, $text, $type, $required)
    {
        $table = "questions";
        $columns = ["form_id", "question_text", "answer_type", "is_required", "created_at"];
        $values = [$formId, $text, $type, $required, date('Y-m-d H:i:s')];
        return $this->db->insertInto($table, $columns, $values, True);
    }

    public function addOption($questionId, $text): string
    {
        $table = "choices";
        $columns = ["question_id", "option_text", "created_at"];
        $values = [$questionId, $text, date('Y-m-d H:i:s')];
        return $this->db->insertInto($table, $columns, $values, True);
    }

    public function getForm($formId)
    {
        $sql = "SELECT * FROM forms WHERE id = ?";
        return $this->db->searchQuery($sql, [$formId])->fetch_assoc();
    }

    public function getQuestions($formId)
    {
        $sql = "SELECT * FROM questions WHERE form_id = ?";
        return $this->db->searchQuery($sql, [$formId])->fetch_all(MYSQLI_ASSOC);
    }

    public function getFormDetails($formId): array
    {
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

    public function getActiveForms()
    {
        $sql = "SELECT * FROM forms WHERE is_active = 1";
        return $this->db->searchQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function submitResponse($formId, $respondentId, $answers)
    {
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

    //get all forms created by a user by id
    public function getUserForms($userId)
    {
        $sql = "SELECT id, title, description, created_at, is_active FROM forms WHERE user_id = ? ORDER BY created_at desc";
        $stmt = $this->db->searchQuery($sql, [$userId]);
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch all responses and answers for a specific form
    public function getFormResponses($formId)
    {
        // Fetch form responses
        $responsesQuery = "SELECT id AS response_id, respondednt_id, created_at FROM responses WHERE form_id = ?";
        $responsesStmt = $this->db->searchQuery($responsesQuery, [$formId]);
        $responses = $responsesStmt->fetch_all(MYSQLI_ASSOC);

        foreach ($responses as &$response) {
            $answersQuery = "
                SELECT q.question_text, a.answer_text 
                FROM answers a
                JOIN questions q ON a.question_id = q.id
                WHERE a.response_id = ?";
            $answersStmt = $this->db->searchQuery($answersQuery, [$response['response_id']]);
            $response['answers'] = $answersStmt->fetch_all(MYSQLI_ASSOC);
        }

        return $responses;
    }

    // (for charts or quick view)
    public function getFormResponseSummary($formId)
    {
        $summaryQuery = "
            SELECT 
                q.id AS question_id, q.question_text, 
                a.answer_text, COUNT(a.answer_text) AS response_count
            FROM questions q
            LEFT JOIN answers a ON q.id = a.question_id
            WHERE q.form_id = ?
            GROUP BY q.id, a.answer_text
            ORDER BY q.id, response_count DESC";
        $summaryStmt = $this->db->searchQuery($summaryQuery, [$formId]);
        return $summaryStmt->fetch_all(MYSQLI_ASSOC);
    }

    public function getQuestionsWithOptions($formId)
    {
        // Fetch all questions for the given form
        $questionsQuery = "SELECT * FROM questions WHERE form_id = ?";
        $questionsStmt = $this->db->searchQuery($questionsQuery, [$formId]);
        $questions = $questionsStmt->fetch_all(MYSQLI_ASSOC);

        // Fetch choices for each question, if applicable
        foreach ($questions as &$question) {
            if (in_array($question['answer_type'], ['multiple_choice', 'checkbox'])) {
                $choicesQuery = "SELECT option_text FROM choices WHERE question_id = ?";
                $choicesStmt = $this->db->searchQuery($choicesQuery, [$question['id']]);
                $question['options'] = array_column($choicesStmt->fetch_all(MYSQLI_ASSOC), 'option_text'); // Extract only option_text
            } else {
                $question['options'] = []; // For text-based or other question types
            }
        }

        return $questions;
    }
    public function deleteQuestions(array $questionIds): bool
    {
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));

        // Delete related options
        $deleteOptionsQuery = "DELETE FROM choices WHERE question_id IN ($placeholders)";
        if (!$this->db->searchQuery($deleteOptionsQuery, $questionIds)) {
            return false;
        }

        // Delete questions
        $deleteQuestionsQuery = "DELETE FROM questions WHERE id IN ($placeholders)";
        return $this->db->searchQuery($deleteQuestionsQuery, $questionIds) ? true : false;
    }
}
