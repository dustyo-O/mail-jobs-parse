<?php
mb_internal_encoding('utf-8');
function fetch_email($data)
{
  var_dump($data);
  foreach($data as $d) {
    if (mb_strpos($d->text,'@')) return $d->text;
  }
}

error_reporting(E_ALL);
ini_set('display_errors',1);
$settings = [
    'email' => 'dustyoo@yandex.ru',
    'server' => 'imap.yandex.ru',
    'port' => 993,
    'protocol' => 'imap',
    'username' => 'dustyoO',
    'password' => '_Fktrcfylh1983!'
];

$emails_from = ['arshinsv@gmail.com', 'monster@email.recjobs.monster.com'];

$conn = "{{$settings['server']}:{$settings['port']}/{$settings['protocol']}/ssl}INBOX";

echo $conn;

$connection = imap_open(
  $conn,
  $settings['username'],
  $settings['password']
);

if ($connection)
{
  echo 'ok';
}

$inbox = imap_check($connection);

$msg_count = $inbox->Nmsgs;

$start = $msg_count - 100;


$result = imap_fetch_overview($connection,"{$start}:{$msg_count}",0);
foreach ($result as $overview) {

  $header = imap_headerinfo($connection, $overview->msgno);
  $fromaddr = $header->from[0]->mailbox . "@" . $header->from[0]->host;


  if (in_array($fromaddr, $emails_from))
  {
    echo $fromaddr."<br/>";
    $content = imap_body($connection, $overview->msgno);
    preg_match_all("/<td\\sstyle=3D\"color:#2a2a2a[^<]+?<a\\shref=3D\"([^\"]+?)\"[^<]+?<emsgscriptdtvalue[^>]+>([^<]+?)<\/emsgscriptdtvalue/ims", $content, $matches);
    foreach ($matches[1] as $key => $value) {
      echo "<a href=\"{$matches[1][$key]}\">{$matches[2][$key]}</a><br/>";
    }
    echo "<br/>";
    echo "____________________________________________";
    echo "<br/>";
  }


}
