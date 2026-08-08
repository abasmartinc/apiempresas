<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if($db->connect_error) die('Connection failed');

$query = "SELECT event_name, page, COUNT(*) as count 
          FROM tracking_events 
          WHERE event_name LIKE '%wordpress%' 
             OR event_name LIKE '%plugin%' 
             OR event_name LIKE '%sheets%'
             OR event_name LIKE '%excel%'
             OR page LIKE '%wordpress%'
             OR page LIKE '%sheets%'
             OR element LIKE '%wordpress%'
             OR element LIKE '%sheets%'
          GROUP BY event_name, page 
          ORDER BY count DESC";

$res = $db->query($query);
echo "Resultados Específicos (WP/Sheets):\n";
while($row = $res->fetch_assoc()) {
    echo "Event: {$row['event_name']} | Page: {$row['page']} | Count: {$row['count']}\n";
}

$query2 = "SELECT event_name, COUNT(*) as count FROM tracking_events GROUP BY event_name ORDER BY count DESC LIMIT 20";
$res2 = $db->query($query2);
echo "\nTop 20 Eventos Generales:\n";
while($row = $res2->fetch_assoc()) {
    echo "Event: {$row['event_name']} | Count: {$row['count']}\n";
}
