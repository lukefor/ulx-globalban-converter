<?php

// from sourcebans
class KvParser
{
	private $fhand;
	private $fend = false;
	private $comment = false;
	private $turnoffcomment = false;
	private $level = 0;
	private $keyname = array();
	private $keyset = array();
	private $mykey = array();

	public function GetArray($file)
	{
		$this->OpenFile($file);
		while(!$this->fend)
		{
			$line = $this->ReadLine();
			$pos = 0;
			$len = strlen($line);
			while($pos<$len)
			{
				if($this->turnoffcomment == true)
				{
					$this->comment = false;
					$this->turnoffcomment = false;
				}
				$char = substr ( $line , $pos, 1);
				if($char == " " || $char == "\t" || $char == "\r" || $char == "\n" ) {$pos++; continue; }
				switch($char)
				{
					case "/":
						$char2 = substr($line , $pos, 2);
						if($char2 == "/*") {
							$this->comment = true;
							break;
						}
						$char2 = substr ( $line , $pos-1, 2);
						if($char2 == "*/" && $this->comment == true ) 
						{
							$this->turnoffcomment = true;
							break;
						}
					
				}
				if($this->comment) { $pos++; continue; }
				
				switch($char)
				{
					case "{":
						$this->level++;
						$this->keyset[$this->level] = false;
						break;
					case "}":
						$this->level--;
						$this->keyset[$this->level] = false;
						break;
					case "\"":
						$pos2 = strpos($line , "\"", $pos+1);
						$val = substr ($line, $pos+1, (($pos2-1)-($pos)));
						$pos = $pos2;
						
						if($this->keyset[$this->level] == false) {
							$this->keyname[$this->level] = $val;
							$this->keyset[$this->level] = true;
						}
						else {
							$this->SetKeyVal($val,$this->level);
							$this->keyset[$this->level] = false;
						}
						
				}
				$pos++;
			}
			
		}
		$this->CloseFile();
		return $this->mykey;
	}
	
	private function SetKeyVal($val,$lvl)
	{
		$arr = array();
		$arr = $this->RecSet($val,$lvl,$arr);
		$this->mykey = array_merge_recursive($this->mykey, $arr);
	}
	
	private function RecSet($val,$lvl,$array,$my=-1)
	{
		$my++;
		if($my==$lvl)
		{
			$array[$this->keyname[$my]] = $val;
		}
		else
		{
			$array[$this->keyname[$my]] = $this->RecSet($val,$lvl,$array,$my);
		}
		return $array;
	}
	private function ReadLine()
	{
		if($this->fend == TRUE) return;
		if (($buf = fgets($this->fhand)) === false) {
			$this->fend = true;
		} else {
			if (feof($this->fhand)) $this->fend = true;
			else return $buf;
		}
	}
	private function OpenFile($file)
	{
		$this->comment = false;
		$this->turnoffcomment = false;
		$this->keyname = array();
		$this->keyset = array();
		$this->mykey = array();
		$this->keyname[0] = "Admins";
		$this->level = 0;
		$this->fhand = @fopen($file, "r");
		if($this->fhand == FALSE) $fend = true;
	}
	private function CloseFile()
	{
		if($this->fhand != FALSE) fclose($this->fhand);
	}
}  