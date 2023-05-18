<?php

namespace App\Models;

use PDO;
use \App\Config;
use \Core\View;

/**
 * Incomes model
 *
 * PHP version 8.2
 */
class Expenses extends \Core\Model
{
    /**
     * expenses table id
     * 
     * @var string
     */
    public $id;

    /**
     * expenses table user_id
     * 
     * @var string
     */
    public $user_id;

    /**
     * expenses table expense_category_assigned_to_user_id
     * 
     * @var string
     */
    public $expense_category_assigned_to_user_id;

    /**
     * expenses table payment_method_assigned_to_user_id
     * 
     * @var string
     */
    public $payment_method_assigned_to_user_id;

    /**
     * expenses table amount
     * 
     * @var string
     */
    public $amount;

    /**
     * expenses table date_of_income
     * 
     * @var string
     */
    public $date_of_expense;

    /**
     * expenses table income_comment
     * 
     * @var string
     */
    public $expense_comment;

    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];

    /**
     * Class constructor
     *
     * @param array $data  Initial property values (optional)
     *
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    /**
     * Save the user model with the current property values
     *
     * @return boolean  True if the user was saved, false otherwise
     */
    public function add()
    {
        $this->validate();

        if (empty($this->errors)) {

            $user_id = $_SESSION['user_id'];
									 
            $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
                    VALUES (:user_id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id, :amount, :date_of_expense, :expense_comment)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindValue(':expense_category_assigned_to_user_id', $this->expense_category_assigned_to_user_id, PDO::PARAM_STR);
            $stmt->bindValue(':payment_method_assigned_to_user_id', $this->payment_method_assigned_to_user_id, PDO::PARAM_STR);
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date_of_expense', $this->date_of_expense, PDO::PARAM_STR);
            $stmt->bindValue(':expense_comment', $this->expense_comment, PDO::PARAM_STR);            

            return $stmt->execute();
        }

        return false;
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validate()
    {
        // Amount
        if (floatval($this->amount) <= 0.0) {
            $this->errors[] = 'Kwota jest zbyt niska';
        }
        if (floatval($this->amount) > 999999.99) {
            $this->errors[] = 'Kwota jest zbyt wysoka';
        }
        // Expense category
        if ($this->expense_category_assigned_to_user_id === 'wybierz') {
            $this->errors[] = 'Nie wybrano kategorii';
        }
        // Payment method
        if ($this->payment_method_assigned_to_user_id === 'wybierz') {
            $this->errors[] = 'Nie wybrano metody płatności';
        }
    }

    /**
     * Select expenses from expenses table between dates
     * 
     * @return object
     */
    public static function getExpenses($user_id, $date_start, $date_end) 
    {
        $sql = 'SELECT e.amount, e.date_of_expense, e.expense_comment, eu.name, pu.name 
                FROM expenses AS e, expenses_category_assigned_to_users AS eu, payment_methods_assigned_to_users AS pu
                WHERE e.user_id = :user_id 
                AND e.date_of_expense BETWEEN :date_start AND :date_end
                AND e.expense_category_assigned_to_user_id = eu.id
                AND e.payment_method_assigned_to_user_id = pu.id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_NUM);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Select expenses sum group by categories
     * 
     * @return array
     */
    public static function getExpensesSumGroupedByCategories($user_id, $date_start, $date_end)
    {
        $sql = 'SELECT eu.name, SUM(e.amount) AS amount_sum
                FROM expenses AS e, expenses_category_assigned_to_users AS eu
                WHERE e.user_id = :user_id
                AND e.date_of_expense BETWEEN :date_start AND :date_end
                AND e.expense_category_assigned_to_user_id = eu.id 
                GROUP BY eu.name 
                ORDER BY amount_sum DESC';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_NUM);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Select expenses sum
     * 
     * @return float
     */
    public static function getExpensesSum($user_id, $date_start, $date_end)
    {
        $sql = 'SELECT SUM(e.amount) AS amount_sum
                FROM expenses AS e
                WHERE e.user_id = :user_id
                AND e.date_of_expense BETWEEN :date_start AND :date_end';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->bindValue(':date_start', $date_start, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $date_end, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch();
    }
}