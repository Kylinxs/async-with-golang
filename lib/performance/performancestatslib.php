
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

/**
 * Class PerformanceStatsLib
 */
class PerformanceStatsLib extends TikiLib
{
    const TYPE_NAVIGATION = 'navigation';

    /**
     * Insert a performance record on the table
     * @param string $url
     * @param int $time_taken
     * @return array|bool|mixed
     */
    public function addRecord(string $url, int $time_taken)
    {
        return $this->table('tiki_performance')->insert([
            'url' => $url,
            'time_taken' => $time_taken
        ]);
    }

    /**
     * Clear all performance records from the database
     * @return TikiDb_Pdo_Result
     */
    public function clearPerformanceRecords()
    {
        return $this->query('DELETE FROM tiki_performance');
    }

    /**
     * Get performance requests based on the average amount of request time
     * @param int $amount
     * @param int $offset
     * @param string $find
     * @param string $order
     * @return TikiDb_Pdo_Result
     */
    public function getRequestsBasedOnAverageRequestTime(int $amount = 25, int $offset = 0, string $find = '', string $order = 'DESC')
    {
        return $this->query("SELECT url, round(AVG(time_taken)) AS average_time_taken FROM tiki_performance WHERE url LIKE ? GROUP BY url ORDER BY average_time_taken $order LIMIT $amount OFFSET $offset", ["%$find%"]);
    }

    /**
     * Get performance requests based on the maximum request time
     * @param int $amount
     * @param int $offset
     * @param string $find
     * @param string $order
     * @return TikiDb_Pdo_Result
     */
    public function getRequestsBasedOnMaximumProcessingTime(int $amount = 25, int $offset = 0, string $find = '', string $order = 'DESC')
    {
        return $this->query("SELECT url, MAX(time_taken) AS maximum_time_taken FROM tiki_performance WHERE url LIKE ? GROUP BY url ORDER BY maximum_time_taken $order LIMIT $amount OFFSET $offset", ["%$find%"]);
    }

    /**
     * Get the amount of requests existent in the DB grouped by URL
     * @param string $find
     * @return array|bool|mixed
     */
    public function getRequestsGroupedByAmount(string $find = '')
    {
        return $this->getOne('SELECT COUNT(DISTINCT(url)) FROM tiki_performance');
    }

    /**
     * Check if a certain performance related call should be logged
     * @param $type
     * @param $url
     * @return bool
     */
    public function shouldLog($type, $url)
    {
        return $type !== null && ! preg_match('/(tiki-admin)\.php/', $url);
    }

    /**
     * Simplify URL to be displayed
     * @param $url
     * @return mixed
     */
    public function simplifyURL($url)
    {
        global $base_url;
        return str_replace($base_url, '', $url);
    }
}