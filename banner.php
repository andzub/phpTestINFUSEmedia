<?php

session_start();
$httpReferer = 'views_' . $_SERVER['HTTP_REFERER'];
if (isset($_SESSION[$httpReferer])) {
    ++$_SESSION[$httpReferer];
} else {
    $_SESSION[$httpReferer] = 1;
}

function getUserIp(): string
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function getUserAgent(): string
{
    return $_SERVER['HTTP_USER_AGENT'];
}

function getPageUrl(): string
{
    return $_SERVER['HTTP_REFERER'];
}

function getViewCount(string $httpReferer): int
{
    return $_SESSION[$httpReferer];
}

function getConnectionToMysql(): PDO
{
    $user = "user";
    $password = "password";

    try {
        $conn = new PDO('mysql:host=localhost;dbname=test', $user, $password);
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage();
        die;
    }

    return $conn;
}

function displayImage() {
    header('Content-Type: image/jpeg');
    $imagePath = 'https://s.dou.ua/CACHE/images/img/static/gallery2/IMG_1045/e740a4351865ad3aefd8f4c8279feff1.jpeg';

    return file_get_contents($imagePath);
}

$userIp = getUserIp();
$userAgent = getUserAgent();
$dateTime = date('Y-m-d H:i:s');
$pageUrl = getPageUrl();
$viewCount = getViewCount($httpReferer);

$conn = getConnectionToMysql();
$stmt = $conn->prepare("SELECT * FROM logs WHERE ip_address=? AND user_agent=? AND page_url=?");
$stmt->execute([$userIp, $userAgent, $pageUrl]);
$user = $stmt->fetch();
if ($user) {
    $sql = "UPDATE logs SET view_date=?, views_count=? WHERE ip_address=? AND user_agent=? AND page_url=?";
    $stmt= $conn->prepare($sql);
    $stmt->execute([$dateTime, $viewCount, $userIp, $userAgent, $pageUrl]);
} else {
    $sql = "INSERT INTO logs (ip_address, user_agent, view_date, page_url, views_count) VALUES (?, ?, ?, ?, ?)";
    $stmt= $conn->prepare($sql);
    $stmt->execute([$userIp, $userAgent, $dateTime, $pageUrl, $viewCount]);
}

echo displayImage();
