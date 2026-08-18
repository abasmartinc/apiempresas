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
            $builder->select('companies.id, companies.cif, companies.company_name as name, companies.cnae_code as cnae, companies.registro_mercantil as province, companies.objeto_social as corporate_purpose, company_enrichment.ai_seo_text, (SELECT COUNT(id) FROM company_administrators WHERE company_administrators.company_id = companies.id) AS num_admins, (SELECT COUNT(id) FROM borme_posts WHERE borme_posts.company_id = companies.id) AS num_borme_posts')
                                 ->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left')
                                 ->join('company_privacy_optouts', 'company_privacy_optouts.cif = companies.cif COLLATE utf8mb4_general_ci', 'left', false)
                                 ->where('company_privacy_optouts.cif IS NULL')
                                 ->where('companies.id >', $lastId);
                                 
            $companies = $builder->orderBy('companies.id', 'ASC')
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

        // ==========================================
        // 2. GENERATE AI-READY SITEMAP
        // ==========================================
        CLI::write("Starting AI-Ready sitemap generation...", 'green');
        
        $aiBuilder = $db->table('companies');
                  
        $lastAiId = 0;
        $aiFileIndex = 1;
        $aiUrlCount = 0;
        
        $currentAiFile = $publicPath . "sitemap-ai-ready-{$aiFileIndex}.xml";
        $xmlAiContent = $xmlHeader;
        $totalAiIncluded = 0;

        while (true) {
            $aiBuilder->select('companies.id, companies.cif, companies.company_name as name, companies.cnae_code as cnae, companies.registro_mercantil as province, companies.objeto_social as corporate_purpose, company_enrichment.ai_seo_text, company_enrichment.updated_at, (SELECT COUNT(id) FROM company_administrators WHERE company_administrators.company_id = companies.id) AS num_admins, (SELECT COUNT(id) FROM borme_posts WHERE borme_posts.company_id = companies.id) AS num_borme_posts')
                                     ->join('company_enrichment', 'company_enrichment.company_id = companies.id')
                                     ->join('company_privacy_optouts', 'company_privacy_optouts.cif = companies.cif COLLATE utf8mb4_general_ci', 'left', false)
                                     ->where('company_privacy_optouts.cif IS NULL')
                                     ->where('company_enrichment.ai_seo_text IS NOT NULL')
                                     ->where("company_enrichment.ai_seo_text != ''")
                                     ->where('companies.id >', $lastAiId);
                                     
            $aiCompanies = $aiBuilder->orderBy('companies.id', 'ASC')
                                     ->limit($batchSize)
                                     ->get()
                                     ->getResultArray();
                                     
            if (empty($aiCompanies)) {
                break;
            }

            foreach ($aiCompanies as $company) {
                $lastAiId = $company['id'];

                if (!shouldIndexCompany($company)) {
                    continue;
                }

                $url = company_url($company);
                $priority = '1.0'; // High priority because it has AI text
                $lastMod = !empty($company['updated_at']) ? date('Y-m-d', strtotime($company['updated_at'])) : date('Y-m-d');
                
                $urlEntry = '<url>' . PHP_EOL . '  <loc>' . esc($url) . '</loc>' . PHP_EOL . '  <lastmod>' . $lastMod . '</lastmod>' . PHP_EOL . '  <changefreq>weekly</changefreq>' . PHP_EOL . '  <priority>' . $priority . '</priority>' . PHP_EOL . '</url>' . PHP_EOL;
                
                $xmlAiContent .= $urlEntry;
                $aiUrlCount++;
                $totalAiIncluded++;
                
                if ($aiUrlCount >= $urlsPerFile) {
                    $xmlAiContent .= '</urlset>';
                    file_put_contents($currentAiFile, $xmlAiContent);
                    
                    CLI::write("Generated AI sitemap {$aiFileIndex} with {$aiUrlCount} URLs.", 'yellow');
                    
                    $aiFileIndex++;
                    $aiUrlCount = 0;
                    $currentAiFile = $publicPath . "sitemap-ai-ready-{$aiFileIndex}.xml";
                    $xmlAiContent = $xmlHeader;
                }
            }
        }

        if ($aiUrlCount > 0) {
            $xmlAiContent .= '</urlset>';
            file_put_contents($currentAiFile, $xmlAiContent);
            CLI::write("Generated AI sitemap {$aiFileIndex} with {$aiUrlCount} URLs.", 'yellow');
        }
        
        file_put_contents($publicPath . 'sitemap-ai-ready-count.txt', $aiFileIndex);
        
        $existingAi = glob($publicPath . 'sitemap-ai-ready-*.xml');
        foreach ($existingAi as $file) {
            if (preg_match('/sitemap-ai-ready-(\d+)\.xml$/', $file, $matches)) {
                $num = (int)$matches[1];
                if ($num > $aiFileIndex) {
                    @unlink($file);
                }
            }
        }

        CLI::write("Done! Included {$totalAiIncluded} AI-enriched companies in {$aiFileIndex} sitemap files.", 'green');
    }
}
