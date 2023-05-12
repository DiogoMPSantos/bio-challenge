<?php

namespace App\Http\Controllers;

use App\Models\Amount;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverCheckboxes;
use Facebook\WebDriver\WebDriverRadios;
use Facebook\WebDriver\WebDriverSelect;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class AmountController extends Controller
{

    public function table()
    {
        // This is where Selenium server 2/3 listens by default. For Selenium 4, Chromedriver or Geckodriver, use http://localhost:4444/
        $host = 'http://localhost:4444/';

        $capabilities = DesiredCapabilities::chrome();

        $driver = RemoteWebDriver::create($host, $capabilities);

        // navigate to table page
        $driver->get('https://testpages.herokuapp.com/styled/tag/table.html');

        $cells = $driver->findElements(WebDriverBy::tagName('td'));

        for ($i=0; $i < count($cells) ; $i = $i+2) { 
            $name = $cells[$i]->getText();
            $amount = $cells[$i+1]->getText();

            try {
                $result = Amount::create([
                    'name' => $name,
                    'amount' => $amount
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        // terminate the session and close the browser
        $driver->quit();

        $amount_table = Amount::all();

        return response()->json(['Dados da Tabela' => $amount_table]);

    }

    public function form()
    {
        // This is where Selenium server 2/3 listens by default. For Selenium 4, Chromedriver or Geckodriver, use http://localhost:4444/
        $host = 'http://localhost:4444/';

        $capabilities = DesiredCapabilities::chrome();

        $driver = RemoteWebDriver::create($host, $capabilities);

        // navigate to form page
        $driver->get('https://testpages.herokuapp.com/styled/basic-html-form-test.html');

        $driver->findElement(WebDriverBy::name('username'))->sendKeys('Diogo');
        $driver->findElement(WebDriverBy::name('password'))->sendKeys('Sua senha e fraca');
        $driver->findElement(WebDriverBy::name('comments'))->clear();
        $driver->findElement(WebDriverBy::name('comments'))->sendKeys('A vinganca nunca e plena');

        $fileInput = $driver->findElement(WebDriverBy::name('filename'));
        $fileInput->setFileDetector(new LocalFileDetector());
        $path = Storage::path('Profile.pdf');
        $fileInput->sendKeys($path);

        $checkboxElement = $driver->findElement(WebDriverBy::name('checkboxes[]'));
        $checkbox = new WebDriverCheckboxes($checkboxElement);
        $checkbox->selectByValue('cb1');
        $checkbox->selectByValue('cb2');

        $radioElement = $driver->findElement(WebDriverBy::name('radioval'));
        $checkbox = new WebDriverRadios($radioElement);
        $checkbox->selectByValue('rd1');

        $multiSelectElement = $driver->findElement(WebDriverBy::name('multipleselect[]'));
        $select = new WebDriverSelect($multiSelectElement);
        $select->selectByValue('ms1');
        $select->selectByValue('ms2');
        $select->selectByValue('ms3');

        $selectElement = $driver->findElement(WebDriverBy::name('dropdown'));
        $select = new WebDriverSelect($selectElement);
        $select->selectByValue('dd1');
        
        $driver->findElement(WebDriverBy::xpath('//*[@id="HTMLFormElements"]/table/tbody/tr[9]/td/input[2]'))->click(); 

        $explanation = $driver->findElement(WebDriverBy::className('explanation')); 
        $response = $explanation->getText();
        $status = $response == 'You submitted a form. The details below show the values you entered for processing.' ? true : false; 

        // terminate the session and close the browser
        $driver->quit();

        return response()->json(['Retorno do Preenchimento' => $response, 'status' => $status]);

    }

    public function download()
    {
        // This is where Selenium server 2/3 listens by default. For Selenium 4, Chromedriver or Geckodriver, use http://localhost:4444/
        $host = 'http://localhost:4444/';

        $options = new ChromeOptions();
        $options->setExperimentalOption('prefs', ['download.default_directory' => storage_path('app\public')]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $options);
        $driver = RemoteWebDriver::create($host, $capabilities);

        // navigate to download page
        $driver->get('https://testpages.herokuapp.com/styled/download/download.html');
        $driver->findElement(WebDriverBy::id('direct-download'))->click(); 

        sleep(3);
        Storage::move('textfile.txt', 'Teste TKS.txt'); 
        
        // terminate the session and close the browser
        $driver->quit();

        return response()->json(['Dados da Tabela' => 'oi']);

    }

    public function upload()
    {
        // This is where Selenium server 2/3 listens by default. For Selenium 4, Chromedriver or Geckodriver, use http://localhost:4444/
        $host = 'http://localhost:4444/';

        $capabilities = DesiredCapabilities::chrome();
        $driver = RemoteWebDriver::create($host, $capabilities);

        // navigate to upload page
        $driver->get('https://testpages.herokuapp.com/styled/file-upload-test.html');
        
        $fileInput = $driver->findElement(WebDriverBy::id('fileinput'));
        $fileInput->setFileDetector(new LocalFileDetector());
        $path = Storage::path('Teste TKS.txt');
        $fileInput->sendKeys($path);

        $radioElement = $driver->findElement(WebDriverBy::id('itsanimage'));
        $checkbox = new WebDriverRadios($radioElement);
        $checkbox->selectByValue('text');
        
        $driver->findElement(WebDriverBy::name('upload'))->click(); 

        $explanation = $driver->findElement(WebDriverBy::className('explanation')); 
        $response = $explanation->getText();
        $status = $response == 'You uploaded a file. This is the result.' ? true : false; 

        // terminate the session and close the browser
        $driver->quit();

        return response()->json(['Retorno do Preenchimento' => $response, 'status' => $status]);

    }

    public function pdf()
    {   

        $pdfParser = new Parser();
        $path = Storage::path('Leitura PDF.pdf');
        $pdf = $pdfParser->parseFile($path);
        $pages = $pdf->getPages();

        foreach ($pages as $key => $page) {
            
            if ($key == 0) {

                $content = $page->getText();
                $content = preg_replace("/\r|\n/", "", $content);

                preg_match_all('/ANS([0-9]*)/m', $content, $matches);
                $ans = $matches[1][0];

                preg_match_all('/Nome da Operadora([A-Z ]* [\w\/]*)/m', $content, $matches);
                $nomeOp = $matches[1][0];

                preg_match_all('/Código na Operadora([0-9]*)/m', $content, $matches);
                $codigoOp = $matches[1][0];

                preg_match_all('/Código na Operadora([0-9 ]*- [A-Z .-]*[0-9]*)/m', $content, $matches);
                $nomeContratado = $matches[1][0];

                preg_match_all('/Código CNES([0-9]{1})/m', $content, $matches);
                $nLote = $matches[1][0];

                preg_match_all('/Número do Lote([0-9]{7})/m', $content, $matches);
                $nProtocolo = $matches[1][0];
                
                preg_match_all('/Nº do Protocolo \(Processo\)([0-9\/]*)/m', $content, $matches);
                $dataProtocolo = $matches[1][0];

                preg_match_all('/Código da Glosa do Protocolo()/m', $content, $matches);
                $codGlosa = $matches[1][0];

                preg_match_all('/Valor Informado do Protocolo (\([A-Z$\) .,0-9]{13})/m', $content, $matches);
                $viProtocolo = $matches[1][0];
                $vpProtocolo = $matches[1][0];
                $vlProtocolo = $matches[1][0];

                preg_match_all('/Valor Informado do Protocolo (\([A-Z$\) .,0-9]{13})([A-Z$\) .,0-9]{11})([0-9.,]*)/m', $content, $matches);
                $vgProtocolo = $matches[3][0];
                $vgGeral = $matches[3][0];

                preg_match_all('/Valor Informado Geral (\([A-Z$\) .,0-9]{13})/m', $content, $matches);
                $viGeral = $matches[1][0];
                $vpGeral = $matches[1][0];
                $vlGeral = $matches[1][0];

                $header = array(
                    "Registro ANS", "Nome da Operadora", "Código na Operadora", 
                    "Nome do Contratado", "Número do Lote", "Número do Protocolo", "Data do Protocolo", "Código  da Glosa do Protocolo",
                    "Valor Informado do Protocolo", "Valor Processado do Protocolo", "Valor Liberado do Protocolo", "Valor Glosa do Protocolo", 
                    "Valor Informado Geral", "Valor Processado Geral", "Valor Liberado Geral","Valor Glosa Geral"

                );

                $first_row = array(
                    $ans, $nomeOp, $codigoOp, $nomeContratado, $nLote, $nProtocolo, $dataProtocolo, $codGlosa,
                    $viProtocolo, $vpProtocolo, $vlProtocolo, $vgProtocolo, $viGeral, $vpGeral, $vlGeral, $vgGeral
                );

                $file = fopen('php://output', 'wb');
                fputcsv($file, $header);
                fputcsv($file,["\n"]);
                fputcsv($file, $first_row);
                fputcsv($file,["\n"]);
            }
            else if ($key == 1) { //Pagina generica
                continue;
            }else{

                $content = $page->getText();

                preg_match_all('/Número da Guia no Prestador([0-9]*)/m', $content, $matches);
                $ngPrestador = $matches[1][0] ?? "";

                preg_match_all('/Número da Guia no Prestador([0-9 -]{18})/m', $content, $matches);
                $ngOperadora = $matches[1][0] ?? "";

                preg_match_all('/Senha()/m', $content, $matches);
                $senha = $matches[1][0] ?? "";

                preg_match_all('/Senha([A-Z0-9-  ]{40})/m', $content, $matches);
                $nBeneficiario = $matches[1][0] ?? "";

                preg_match_all('/Nome do Beneficiário([0-9 ]{27})/m', $content, $matches);
                $nCarteira = $matches[1][0] ?? "";

                preg_match_all('/Número da Carteira([0-9\/]*)/m', $content, $matches);
                $dataInicioFaturamento = $matches[1][0] ?? "";

                preg_match_all('/Data Início do Faturamento([0-9\/]*)/m', $content, $matches);
                $dataFimFaturamento = $matches[1][0] ?? "";

                preg_match_all('/Data Fim do Faturamento([0-9:]*)/m', $content, $matches);
                $horaInicioFaturamento = $matches[1][0] ?? "";

                preg_match_all('/Hora Início do Faturamento([0-9:]*)/m', $content, $matches);
                $horaFimFaturamento = $matches[1][0] ?? "";

                preg_match_all('/Código da Glosa da Guia()/m', $content, $matches);
                $cgGuia = $matches[1][0] ?? "";

                preg_match_all('/Código da Glosa da Guia()/m', $content, $matches);
                $cgGuia = $matches[1][0] ?? "";

                $row = array(
                    $ngPrestador, $ngOperadora, $senha, $nBeneficiario, $nCarteira, $dataInicioFaturamento,
                    $dataFimFaturamento, $horaInicioFaturamento, $horaFimFaturamento, $cgGuia
                );

                $file = fopen('php://output', 'wb');
                fputcsv($file, $row);
                fputcsv($file,["\n"]);

            }

        }

        
        // header('Content-Type: text/csv');
        // header('Content-Disposition: attachment; filename="sample.csv"');
        // fclose($file);

        return response()->json(['salve']);
    }
}
