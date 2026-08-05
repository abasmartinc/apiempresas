<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CompanyModel;

class GenerateSitemaps extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'SEO';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'sitemap:generate';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates static sitemaps for all companies to avoid timeouts.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sitemap:generate';

    public function run(array $params)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        
        helper(['text', 'seo_dynamic', 'company']);
        
        CLI::write("Starting sitemap generation...", 'green');
        
        $db = \Config\Database::connect();
        $db->saveQueries = false; // Prevent memory leak from query history
        $builder = $db->table('companies');
        $builder->select('id, cif, company_name as name, cnae_code as cnae, registro_mercantil as province, objeto_social as corporate_purpose');
        
        $lastId = 0;
        $batchSize = 10000;
        
        $fileIndex = 1;
        $urlCount = 0;
        $urlsPerFile = 10000;
        
        $publicPath = WRITEPATH . 'sitemaps/';
        
        $currentFile = $publicPath . "sitemap-companies-{$fileIndex}.xml";
        $currentFileEn = $publicPath . "sitemap-en-companies-{$fileIndex}.xml";
        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        $xmlContent = $xmlHeader;
        $xmlContentEn = $xmlHeader;
        
        $totalProcessed = 0;
        $totalIncluded = 0;

        while (true) {
            $companies = $builder->where('id >', $lastId)
                                 ->orderBy('id', 'ASC')
                                 ->limit($batchSize)
                                 ->get()
                                 ->getResultArray();
                                 
            if (empty($companies)) {
                break; // No more records
            }

            foreach ($companies as $company) {
                $lastId = $company['id'];
                $totalProcessed++;

                if (!shouldIndexCompany($company)) {
                    continue;
                }

                $url = company_url($company);
                $urlEn = str_replace(
                    ['apiempresas.es', 'apiempresas.test'],
                    ['spaincompanyapi.com', 'spaincompanyapi.test'],
                    $url
                );

                $score = calculateCompanySeoScore($company);
                $priority = ($score >= 7) ? '0.8' : '0.6';
                
                $urlEntry = '<url>' . PHP_EOL . '  <loc>' . esc($url) . '</loc>' . PHP_EOL . '  <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL . '  <changefreq>monthly</changefreq>' . PHP_EOL . '  <priority>' . $priority . '</priority>' . PHP_EOL . '</url>' . PHP_EOL;
                $urlEntryEn = '<url>' . PHP_EOL . '  <loc>' . esc($urlEn) . '</loc>' . PHP_EOL . '  <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL . '  <changefreq>monthly</changefreq>' . PHP_EOL . '  <priority>' . $priority . '</priority>' . PHP_EOL . '</url>' . PHP_EOL;
                
                $xmlContent .= $urlEntry;
                $xmlContentEn .= $urlEntryEn;
                
                $urlCount++;
                $totalIncluded++;
                
                // If we reached the limit for one file, write and close
                if ($urlCount >= $urlsPerFile) {
                    $xmlContent .= '</urlset>';
                    $xmlContentEn .= '</urlset>';
                    file_put_contents($currentFile, $xmlContent);
                    file_put_contents($currentFileEn, $xmlContentEn);
                    
                    CLI::write("Generated sitemaps {$fileIndex} (ES & EN) with {$urlCount} URLs.", 'yellow');
                    
                    // Reset for next file
                    $fileIndex++;
                    $urlCount = 0;
                    $currentFile = $publicPath . "sitemap-companies-{$fileIndex}.xml";
                    $currentFileEn = $publicPath . "sitemap-en-companies-{$fileIndex}.xml";
                    $xmlContent = $xmlHeader;
                    $xmlContentEn = $xmlHeader;
                }
            }
            
            CLI::write("Processed {$totalProcessed} companies so far (Last ID: {$lastId})...", 'cyan');
            
            unset($companies);
            gc_collect_cycles();
        }

        // Write the remaining URLs if any
        if ($urlCount > 0) {
            $xmlContent .= '</urlset>';
            $xmlContentEn .= '</urlset>';
            file_put_contents($currentFile, $xmlContent);
            file_put_contents($currentFileEn, $xmlContentEn);
            CLI::write("Generated sitemap {$fileIndex} (ES & EN) with {$urlCount} URLs.", 'yellow');
        }

        CLI::write("Done! Processed {$totalProcessed} total companies, included {$totalIncluded} in {$fileIndex} sitemap files.", 'green');
        
        // Create an index file specifically for these just to keep track of the count
        // So that the main Sitemap Controller knows how many there are.
        file_put_contents($publicPath . 'sitemap-companies-count.txt', $fileIndex);

        // Cleanup any old files that might remain if the total count decreased
        $existing = glob($publicPath . 'sitemap-companies-*.xml');
        $existingEn = glob($publicPath . 'sitemap-en-companies-*.xml');
        $allExisting = array_merge($existing, $existingEn);
        
        foreach ($allExisting as $file) {
            // Extract the number from the filename
            if (preg_match('/sitemap-(?:en-)?companies-(\d+)\.xml$/', $file, $matches)) {
                $num = (int)$matches[1];
                if ($num > $fileIndex) {
                    @unlink($file);
                }
            }
        }
    }
}
