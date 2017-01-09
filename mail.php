<?php
mb_internal_encoding('utf-8');
function fetch_email($data)
{
  var_dump($data);
  foreach($data as $d) {
    if (mb_strpos($d->text,'@')) return $d->text;
  }
}

function inner_content($start, $finish, $string)
{
    $pie = explode($start, $string, 2);
    if (count($pie) === 2)
    {
        $string = $pie[1];
        $pie = explode($finish, $string, 2);
        if (count($pie) === 2)
        {
            return $pie[0];
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

function fetch_location($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE); // We'll parse redirect url from header.
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE); // We want to just get redirect url but not to follow it.
    $response = curl_exec($ch);
    preg_match_all('/^Location:(.*)$/mi', $response, $matches);
    curl_close($ch);
    return !empty($matches[1]) ? trim($matches[1][0]) : $url;
}
require_once "settings.php";
error_reporting(E_ALL);
ini_set('display_errors',1);


$emails_from = ['arshinsv@gmail.com', 'monster@email.recjobs.monster.com'];

$conn = "{{$settings['server']}:{$settings['port']}/{$settings['protocol']}/ssl/novalidate-cert}INBOX";

echo $conn;

$connection = imap_open(
    $conn,
    $settings['username'],
    $settings['password']
);

if ($connection)
{
    $db_link = mysqli_connect($db['host'], $db['user'], $db['password'], $db['db'], $db['port']);

    echo 'ok';
}

$inbox = imap_check($connection);

$msg_count = $inbox->Nmsgs;

$start = $msg_count - 100;

$result = imap_fetch_overview($connection,"{$start}:{$msg_count}",0);

foreach ($result as $overview)
{

    $header = imap_headerinfo($connection, $overview->msgno);
    $fromaddr = $header->from[0]->mailbox . "@" . $header->from[0]->host;


    if (in_array($fromaddr, $emails_from))
    {
        echo $fromaddr."<br/>";
        $content = imap_body($connection, $overview->msgno);

        $vacancies_html = inner_content('request, below are 10 jobs that match the criteria of your job search.',
            '>More Jobs Lik',
            $content);

        preg_match_all("/href=3D\"([^\"]+)\"/ims", $vacancies_html, $matches);
        foreach ($matches[1] as $key => $value) {
            $url = str_replace("=3D", '=', $matches[1][$key]);
            $url = str_replace("=\r\n", '', $url);
            $url = fetch_location($url);
            echo "<a href=\"{$url}\">{$url}</a><br/>";

            $query = "SELECT COUNT(*) FROM `links` WHERE `link` = '{$url}'";
            $result = mysqli_query($db_link, $query);
            if ($row = mysqli_fetch_row($result))
            {
                $count = (int) $row[0];
                if ($count === 0)
                {
                    $query = "INSERT INTO `links` VALUES (NULL, '{$url}')";
                    $result = mysqli_query($db_link, $query);
                }
            }
        }
        echo "<br/>";
        echo "____________________________________________";
        echo "<br/>";
    }


}
