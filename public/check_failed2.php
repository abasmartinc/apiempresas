<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
$res = $db->query("SELECT * FROM seo_generation_queue WHERE status = 'failed' ORDER BY requested_at DESC LIMIT 5");
while($r = $res->fetch_assoc()) {
    print_r($r);
}
$db->close();
