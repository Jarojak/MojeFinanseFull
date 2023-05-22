<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\Incomes;
use \App\Models\Expenses;

/**
 * Balance controller (example)
 *
 * PHP version 7.0
 */
class Balance extends Authenticated
{
    /**
     * incomes table
     * 
     * @var array
     */
    public $incomes;

    /**
     * expenses table
     * 
     * @var array
     */
    public $expenses;
    
    /**
     * incomes sum grouped by categories
     * 
     * @var array
     */
    public $incomes_sum_cat;

    /**
     * expenses sum grouped by categories
     * 
     * @var array
     */
    public $expenses_sum_cat;

    /**
     * incomes sum
     * 
     * @var array
     */
    public $incomes_sum;

    /**
     * expenses sum
     * 
     * @var array
     */
    public $expenses_sum;

    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];

    /**
     * functions
     * 
     * @return void
     */
    private function queries($date_start, $date_end)
    {
        $user_id = $_SESSION['user_id'];

        $this->incomes = Incomes::getIncomes($user_id, $date_start, $date_end);
        $this->expenses = Expenses::getExpenses($user_id, $date_start, $date_end);
        $this->incomes_sum_cat = Incomes::getIncomesSumGroupedByCategories($user_id, $date_start, $date_end);
        $this->expenses_sum_cat = Expenses::getExpensesSumGroupedByCategories($user_id, $date_start, $date_end);
        $this->incomes_sum = Incomes::getIncomesSum($user_id, $date_start, $date_end);
        $this->expenses_sum = Expenses::getExpensesSum($user_id, $date_start, $date_end);
    }

    /**
     * Balance index
     *
     * @return void
     */
    public function indexAction()
    {
        $d=strtotime("-30 Days");
        $date_start = date("Y-m-d", $d);
        $date_end = date("Y-m-d");

        $this->queries($date_start, $date_end);

        View::renderTemplate('Balance/index.html', [
            'balance' => $this,
            'action' => 'ostatnie 30 dni' 
        ]);
    }

    /**
     * Balance index
     *
     * @return void
     */
    public function indexCurrentMonthAction()
    {
        $date_start = date("Y-m-01");
        $date_end = date("Y-m-d");

        $this->queries($date_start, $date_end);

        View::renderTemplate('Balance/index.html', [
            'balance' => $this,
            'action' => 'bieżący miesiąc' 
        ]);
    }

    /**
     * Balance index
     *
     * @return void
     */
    public function indexPreviousMonthAction()
    {
        $d=strtotime("first day of last month");
        $date_start = date("Y-m-d", $d);
        $d=strtotime("last day of last month");
        $date_end = date("Y-m-d", $d);

        $this->queries($date_start, $date_end);

        View::renderTemplate('Balance/index.html', [
            'balance' => $this,
            'action' => 'poprzedni miesiąc' 
        ]);
    }

    /**
     * Balance index
     *
     * @return void
     */
    public function indexSelectedDatesAction()
    {
        $date_start = date($_POST['dateFrom']);
        $date_end = date($_POST['dateTo']);

        $this->queries($date_start, $date_end);

        View::renderTemplate('Balance/index.html', [
            'balance' => $this,
            'action' => $date_start . ' - ' . $date_end 
        ]);
    }
}