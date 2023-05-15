<?php

require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

class ContentCreator {
    private $size = "poster";
    private $theme = "dark";
    private $events = array();
    private $html = "";
    private $dompdf;

    private function buildPdf(){
        $inner = $this->getHeader();
        $inner .= $this->getMainContent();

        $this->html .= $this->getGlobalStyles();
        $this->html .= $this->getBackground();
        $this->html .= $this->contentWrap($inner);
    }

    public function addEvent(){
        $this->events[] = array(
            "art" => "match",
            "datetime" => "14.05.2023 10:00 Uhr",
            "location" => "Sportplatz Tambach-Dietharz",
            "title" => "Fussball am Samstag",
            "abteilung" => "Fussball",
            "heim" => "1. Mannschaft",
            "gast" => "SG Fortuna Remstädt"
        );
    }

    private function getMainContent(){
        $content = $this->getMainContentStyle();
        foreach ($this->events as $event) {
            switch ($event["art"]) {
                case 'match':
                    $content .= $this->getMatchHtml($event["datetime"], $event["location"], $event["title"], $event["abteilung"], $event["heim"], $event["gast"]);
                    break;
                
                case 'date':
                    $content .= "TERMIN";
                    break;
            }
        }
        return $content;
    }

    private function getMainContentStyle(){
        ob_start(); ?>
        <style>
        .event{
            background: rgb(14, 26, 42);
            margin: 10px 0;
            padding: 20px 0 0 20px;
            position: relative;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 2px 2px 0px 0px white;
        }

        .vs-wrap{
            text-align: right;
        }
        .event .match-info{
            position: relative;
            font-size: 1.2em;
            margin-left: auto;
            border-collapse: collapse;
            margin: 15px 0 0 auto;
            vertical-align: middle;
            
        }
        .match-info td{
            vertical-align: middle;
        }
        .match-info .heim-logo, .match-info .gast-logo{
            background: white;
            width: 1em;
            height: 1em;
            display: none;
        }

        .match-info .heim, .match-info .gast{
            background: rgb(26, 53, 80);
            padding: 5px 20px ;
        }
        .match-info .heim{
            border-radius: 5px 0 0 0;
        }

        .match-info .vs{
            background: rgb(227, 39, 64);
            padding: 10px;
            font-size: 0.7em;
            font-weight: bold;
        }

        .evtitle{
            text-align: left;
            font-size: 1.4em;
            font-weight:bold; 
        }
        .datetime{
            text-align: left;
            font-size: 1em;
        }
        .abteilung{
            text-align: right;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        </style>
        <?php return ob_get_clean();
    }

    private function getMatchHtml($datetime, $location, $title, $abteilung, $heim, $gast){
        ob_start(); ?>
        <div class="event match">
            <p class="datetime">14.05. 20:00 Uhr - Sportplatz</p>
            <p class="evtitle">Mini-Meisterschaft</p>
            <p class="abteilung">Abteilung<br>Fußball</p>
            <div class="vs-wrap">
                <table class="match-info">
                    <tr>
                        <td class="heim-logo">H</td>
                        <td class="heim">1. Mannschaft</td>
                        <td class="vs">VS</td>
                        <td class="gast">SG Fortuna Remstädt</td>
                        <td class="gast-logo">G</td>
                    </tr>
                </table>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    private function getHeader(){
        ob_start(); ?>
        <style>
            .svhead{
                text-align: center;
                font-size: 1.1em;
                letter-spacing: 3px;
                font-weight: 100;
            }

            .mainhead{
                text-transform: uppercase;
                text-align: center;
                font-size: 5em;
                margin: 30px 0;
                font-weight: 900;
            }

            h3.date{
                background: white;
                display: inline-block;
                color: black;
                padding: 10px 20px;
                margin: 0 auto;
                font-size: 2em;
                border-radius: 5px;
            }
        </style>
        <div class="header">
            <h2 class="svhead">SV "MOTOR" TAMBACH-DIETHARZ E.V.</h2>
            <h1 class="mainhead">Sportliche Highlights</h1>
            <h3 class="date">14.05.2023 - 18.05.2023</h3>
        </div>
        <?php return ob_get_clean();
    }

    private function contentWrap($content){
        ob_start(); ?>
        <style>
            .content{
                padding: 2cm;
                text-align: center;
                position: relative;
                z-index: 1;
                color: white;
            }
        </style>
        <div class="content">
            <?php echo $content; ?>
        </div>
        <?php return ob_get_clean();
    }

    private function getBackground(){
        ob_start(); ?>
        <style>
            .background{
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 0;
                background: black;
            }
        </style>
        <div class="background"></div>
        <?php return ob_get_clean();
    }

    private function getGlobalStyles(){
        ob_start(); ?>
        <style>
            @page{
                margin: 0;
            }
            body {
                font-family: Arial, Helvetica, sans-serif;
                line-height: 1;
            }
            body *{
                margin: 0;
                box-sizing: border-box;
            }
        </style>
        <?php return ob_get_clean();
    }

    public function create(){

        $this->buildPdf();
        
        $options = new Options();
        //$options->set('isRemoteEnabled', true);
        //$options->set("isPhpEnabled", true);
        $this->dompdf = new Dompdf($options);

        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'portrait');
    }

    public function output(){
        $this->dompdf->render();
        $this->dompdf->stream("dompdf_out.pdf", array("Attachment" => false));
    }
}
