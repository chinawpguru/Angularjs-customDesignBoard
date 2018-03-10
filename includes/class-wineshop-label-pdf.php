<?php
/**
 * Created by PhpStorm.
 * User: bhall
 * Date: 7/30/2017
 * Time: 10:03 PM
 */

require(DUALBRAIN_WINESHOP_LABEL_DESIGNER_PLUGIN_DIR.'includes/lib/fpdf.php');


class Wineshop_Label_PDF extends FPDF
{
	protected $count = 0;
	protected $slots = array(1,2,3,4);
	protected $image;

	protected $image_size = 96.4;

	public function __construct( $image, $slots=null, $count=null )
	{
		parent::__construct( 'P', 'mm', 'Letter' );

		$this->image = $image;

		if($slots)
			$this->slots = $slots;

		if($count)
			$this->count = $count;



		// Everything is set - assemble the images into the template
		$this->assemble();
	}

	public function assemble()
	{
		// Generate the PDF
		if($this->count > 0 )
		{
			// Add Page(s)
			$this->AliasNbPages();
			$this->AddPage();

			// Store the total to print
			$remaining_labels = $this->count;
			$current_page_label = 0;
			//$current_page_slot = 1;

			// Build the first page
			if( count($this->slots) > 0 )
			{
				// Make sure any first-page labels were assigned. If none were available (huh??), just proceed to the next page and leave this blank.
				// Not sure why they'd ever want that...
				foreach ( $this->slots as $key=>$val )
				{
					$this->add_image($this->image, $val);
					$remaining_labels--;
				}

				// Tell the script that the first page is full.
				$current_page_label = 5;
			}

			// See if we have any remaining labels to print. If so, we need to move to the next page.
			while( $remaining_labels > 0 )
			{
				if( $current_page_label > 4 )
				{
					// Last page is full. Add a new one, and reset the active slot
					$this->AddPage();
					$current_page_label = 1;
				}

				// Add the image to the next slot
				$this->add_image($this->image, $current_page_label);

				// Increment to the next slot
				$current_page_label++;

				// Subtract from the totals remaining
				$remaining_labels--;
			}
		}

	}

	protected function add_image($image, $slot)
	{
		// Cast as int
		if( !is_int($slot) )
			$slot = (int)$slot;

		switch($slot)
		{
			case 1:
				$this->Image($image,10,35, $this->image_size);
				break;

			case 2:
				$this->Image($image,110,35, $this->image_size);
				break;

			case 3:
				$this->Image($image,10,140, $this->image_size);
				break;

			case 4:
				$this->Image($image,110,140, $this->image_size);
				break;
		}
	}
}