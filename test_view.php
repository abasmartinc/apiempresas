<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
define("ENVIRONMENT", "development");
require "vendor/autoload.php";
require "app/Config/Constants.php";
require "app/Common.php";

// Let us just do a quick str_replace or include or whatever to see what PHP says.
// Since CodeIgniter is complicated to boot without SiteURI, lets mock the environment:
$cnae_code = "561";
$cnae_label = "Sector 561";
$total_companies = 87301;
$total_formatted = "87.301";
$top_provinces = [];
$title = "Test";
$meta_description = "Test";
$canonical = "test";
$companies = [];

ob_start();
include "app/Views/seo/cnae.php";
ob_end_clean();
echo "SUCCESS";

