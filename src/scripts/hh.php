<?php

const ID_KEY = 0;
const DESCRIPTION_KEY = 4;
const TITLE_KEY = 5;

$vacancies = getAllVacancies();
$vacanciesDataToInsert = parseVacancies($vacancies);

$mysqli = connectToMysql();

foreach ($vacanciesDataToInsert as $countryCode => $vacanciesByCountry) {
    $mysqli = changeDb($mysqli, $countryCode);

    $lastPost = getLastPost($mysqli);
    deleteAllOldPosts($mysqli);

    $lastId = $lastPost[ID_KEY];
    foreach ($vacanciesByCountry as $vacanciesData) {
        $newPostId = $lastId + 1;
        $newPost = $lastPost;
        $newPost[ID_KEY] = $newPostId;
        $newPost[DESCRIPTION_KEY] = $vacanciesData['desc'];
        $newPost[TITLE_KEY] = $vacanciesData['title'];

        $newPostValues = [];
        foreach ($newPost as $field) {
            $newPostValues[] = "\"$field\"";
        }
        $newPostValues = implode(",", $newPostValues);
        $insertQueryPost = "INSERT INTO wp_posts VALUES ($newPostValues)";
        $mysqli->query($insertQueryPost);

        $city = $vacanciesData['city'];
        $shortTitle = $vacanciesData['short_title'];
        $insertQueryPostMeta = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
                             ($newPostId, 'jobs_location', \"$city\"),
                             ($newPostId, 'jobs_short_description', \"$shortTitle\")
                           ";
        $mysqli->query($insertQueryPostMeta);

        $lastId = $newPostId;
    }
}

//HH.ru api functions
function getAllVacancies()
{
    $url = 'https://api.hh.ru/vacancies';

    $params = [
        'employer_id' => '3543358'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36');
    curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);
    return $data;
}

function getVacancyData($vacancyId)
{
    $url = 'https://api.hh.ru/vacancies/' . $vacancyId;

    $params = [
        'employer_id' => '3543358'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36');
    curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));

    $response = curl_exec($ch);
    $vacancyData = json_decode($response, true);
    curl_close($ch);

    return $vacancyData;
}

//ParseData functions
function parseVacancies($vacancies)
{
    $result = [];
    foreach ($vacancies['items'] as $vacancy) {
        $lock = 'ru';
        $vacancyData = getVacancyData($vacancy['id']);

        if (strpos($vacancyData['description'], 'Relocation to Cyprus') !== false
            || strpos($vacancyData['description'], 'Релокация на Кипр') !== false) {
            $lock = 'cy';
        }

        if ($vacancyData['type']['id'] === 'open' && $vacancyData['area']['name'] === 'Санкт-Петербург') {
            $result[$lock][$vacancy['id']]['title'] = $vacancyData['name'];
            $result[$lock][$vacancy['id']]['desc'] = $vacancyData['description'];
            $result[$lock][$vacancy['id']]['short_title'] = $vacancyData['name'];
            $result[$lock][$vacancy['id']]['city'] = 'Санкт-Петербург';
            if ($lock === 'cy') {
                $result[$lock][$vacancy['id']]['city'] = 'Limassol';
            }
        }
    }
    return $result;
}

//Mysql functions
function connectToMysql()
{
    $hostname = "5.11.87.23";
    $username = "wpswat";
    $password = "vrbzw4kQHYQPm";
    $mysqli = new mysqli($hostname, $username, $password);

    $mysqli = changeDb($mysqli, 'ru');
    return $mysqli;
}

function changeDb($mysqli, $country = null)
{
    $dbname['ru'] = "wordpress_russia";
    $dbname['cy'] = "wordpress_cyprus";

    $selectedDb = isset($dbname[$country]) ? $dbname[$country] : $dbname['ru'];
    mysqli_select_db($mysqli, $selectedDb);
    $mysqli->query("SET NAMES 'utf8'");

    return $mysqli;
}

function getLastPost($mysqli)
{
    return $mysqli->query("SELECT * FROM wp_posts WHERE post_type = 'jobs' ORDER BY ID DESC LIMIT 1")->fetch_row();
}

function deleteAllOldPosts($mysqli)
{
    $allPostsIdsWithJobsType = "SELECT id FROM wp_posts WHERE post_type = 'jobs'";
    $postsIds = $mysqli->query($allPostsIdsWithJobsType);

    if (!$postsIds) {
        return;
    }

    $resultPostsIds = [];
    foreach ($postsIds->fetch_all() as $post) {
        $resultPostsIds[] = $post[0];
    }
    $ids = implode(',', $resultPostsIds);

    $deletePostsFromMeta = "DELETE FROM wp_postmeta WHERE post_id IN ($ids)";
    $mysqli->query($deletePostsFromMeta);

    $deletePostsFromPosts = "DELETE FROM wp_posts WHERE post_type = 'jobs'";
    $mysqli->query($deletePostsFromPosts);
}
