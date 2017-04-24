<?php
/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

if (!defined('_PS_VERSION_')) exit;

// load TCPDF library (if not loaded somewhere in Joomla! yet)
if (!class_exists('TCPDF')) require _PS_MODULE_DIR_.'awocoupon/lib/tcpdf/tcpdf_include.php';


class Html2Tcpdf extends TCPDF {

	var $marginTop = 10;
	var $marginBottom = 10;
	var $marginLeft = 10;
	var $marginRight = 10;
	//==============================
	var $y_Footer = 0;
	var $html_header = '';
	var $html_body = '';
	var $html_footer = '';

	public function __construct($html_header,$html_body,$html_footer) {
	
		//parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false, false);
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false);
	
		$this->html_header = $this->convert_utf8($html_header);
		$this->html_body = $this->convert_utf8($html_body);
		$this->html_footer = $this->convert_utf8($html_footer);
        
        $this->SetCreator(PDF_CREATOR);
        
        // disable header and footer
        $this->setPrintHeader(true);
        $this->setPrintFooter(true);
        
        $this->SetMargins($this->marginLeft, $this->marginTop, $this->marginRight);
        // header and footer margins
        $this->setHeaderMargin(PDF_MARGIN_HEADER);
        $this->setFooterMargin(PDF_MARGIN_FOOTER);
        
        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        //set auto page breaks
		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
		$this->SetFont('helvetica', '', 10);
		
		$this->getBottomMarginHeight();
		
	}


	public function processpdf($output_type, $filename=null) {
 		$this->AddPage();
		$this->writeHTML($this->html_body, true, false, true, false, '');
		$this->lastPage();

		//Close and output PDF document
		return $this->Output($filename, $output_type);
	}


    public function Header() {

       //set top margin
       //$this->SetY($this->marginTop); 
       
       $this->writeHTML($this->html_header, false, false, true, false, 'L'); 

       //set top margin based on current header ending
       $this->SetTopMargin ($this->GetY()+5);
    } 

    public function Footer() {
       
        // set distance from bottom
        //$this->SetY($this->y);
		$this->SetY($this->y_Footer*-1);
        $this->writeHTML($this->html_footer, false, false, true, false, 'L');
    }
	
	public function convert_utf8($str) {
		require_once _PS_MODULE_DIR_.'awocoupon/lib/ForceUTF8/Encoding.php';
		return Encoding::toUTF8(str_replace(array(chr(160).chr(194),chr(194).chr(160)),' ',$str));
	}
	public function _convert_utf8($str) {
		$tab = array("ASCII", "Windows-1252", "ISO-8859-15", "ISO-8859-1", "ISO-8859-6", "CP1256"); 
		foreach ($tab as $i) $str = iconv($i, 'UTF-8//IGNORE', $str); 
		return $str; 
	}

	public function getBottomMarginHeight() {
		// pdf2 set x margin to pdf1's xmargin, but y margin to zero
		// to make sure that pdf2 has identical settings, you can clone the object (after initializing the main pdf object)
		$pdf2 = clone $this;
		$pdf2->SetTopMargin(0);
		$pdf2->AddPage();
		$height_before = $pdf2->GetY();
        $pdf2->writeHTML($this->html_footer, false, false, true, false, 'L');
		$height_after = $pdf2->GetY();
		$this->y_Footer = ($height_after-$height_before) + $this->marginBottom;
		$pdf2->deletePage($pdf2->getPage());
	}
 
}
