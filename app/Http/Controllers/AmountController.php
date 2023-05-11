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
}
