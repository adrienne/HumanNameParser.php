<?php
/**
 * Works with a Name object to parse out the parts of a name.
 *
 * Example usage:
 *		$parser = new Parser("John Q. Smith");
 *		echo  $parser->getLast() . ", " . $parser->getFirst();
 *		//returns "Smith, John"
 *
 *
 */
class HumanNameParser_Parser {
	private $name;
	
	private $title;
	private $leadingInit;
	private $first;
	private $nicknames;
	private $middle;
	private $last;
	private $suffix;
	
	private $titles;
	private $suffixes;
	private $prefixes;

	/*
	* Constructor
	*
	* @param mixed $name Either a name as a string or as a Name object.
	*/
	public function __construct($name = NULL)
	{
		$this->setName($name);
	}

	  /**
	  * Sets name string and parses it.
	  * Takes Name object or a simple string (converts the string into a Name obj),
	  * parses and loads its constituant parts.
	  *
	  * @param	mixed $name	Either a name as a string or as a Name object.
	  */
	  public function setName($name = NULL){
		  if ($name) {
		  
			  if (is_object($name) && get_class($name) == "HumanNameParser_Name") { // this is mostly for testing
				  $this->name = $name;
			  }
			  else {
				  $this->name = new HumanNameParser_Name($name);
			  }
			
			  $this->title = "";
			  $this->leadingInit = "";
			  $this->first = "";
			  $this->nicknames = "";
			  $this->middle = "";
			  $this->last = "";
			  $this->suffix = "";






			  $this->titles = array('ms','miss','mrs','mr','master','rev','reverend','fr','father','dr','doctor',
									'atty','attorney','prof','professor','hon','honorable','pres','president',
									'gov','governor','coach','ofc','officer','msgr','monsignor','sr','sister',
									'br','brother','supt','superintendent','rep','representative','sen','senator',
									'amb','ambassador','treas','treasurer','sec','secretary','pvt','private',
									'cpl','corporal','sgt','sargent','adm','administrative','maj','major',
									'capt','captain','cmdr','commander','lt','lieutenant','lt col','lieutenant colonel',
									'col','colonel','gen','general');
			  $this->suffixes = array('esq','esquire','jr','sr','2','ii','iii','iv');
			  $this->prefixes = array('bar','ben','bin','da','dal','de la', 'de', 'del','der','di',
							'ibn','la','le','san','st','ste','van', 'van der', 'van den', 'vel','von');

			  $this->parse();
		  }
	  }
	  
	  public function getleadingInit() {
		  return $this->leadingInit;
	  }
	  public function getFirst() {
		  return $this->first;
	  }
	  public function getNicknames() {
		  return $this->nicknames;
	  }

	  public function getMiddle() {
		  return $this->middle;
	  }

	  public function getLast() {
		  return $this->last;
	  }

	  public function getSuffix() {
		  return $this->suffix;
	  }
          public function getName(){
              return $this->name;
          }

	  /**
	   * returns all the parts of the name as an array
	   *  
	   * @param String $arrType pass 'int' to get an integer-indexed array (default is associative)
	   * @return array An array of the name-parts 
	   */
	  public function getArray($arrType = 'assoc') {
		  $arr = array();
		  $arr['title'] = $this->title;
		  $arr['leadingInit'] = $this->leadingInit;
		  $arr['first'] = $this->first;
		  $arr['nicknames'] = $this->nicknames;
		  $arr['middle'] = $this->middle;
		  $arr['last'] = $this->last;
		  $arr['suffix'] = $this->suffix;
		  if ($arrType == 'assoc') {
			  return $arr;
		  }
		  else if ($arrType == 'int'){
			  return array_values($arr);
		  }
		  else {
			  throw new Exception("Array must be associative ('assoc') or numeric ('num').");
		  }
	  }

	  /*
	   * Parse the name into its constituent parts.
	   *
	   * Sequentially captures each name-part, working in from the ends and
	   * trimming the namestring as it goes.
	   * 
	   * @return boolean	true on success
	   */
	  private function parse() 
	  {
		  $titles = implode("\.*|", $this->titles) . "\.*"; // each suffix gets a "\.*" behind it.
		  $suffixes = implode("\.*|", $this->suffixes) . "\.*"; // each suffix gets a "\.*" behind it.
		  $prefixes = implode(" |", $this->prefixes) . " "; // each prefix gets a " " behind it.

		  // The regex use is a bit tricky.  *Everything* matched by the regex will be replaced,
		  //	but you can select a particular parenthesized submatch to be returned.
		  //	Also, note that each regex requres that the preceding ones have been run, and matches chopped out.
		  $titleRegex = "/($titles)\.?/";
		  $nicknamesRegex =		"/ ('|\"|\(\"*'*)(.+?)('|\"|\"*'*\)) /"; // names that starts or end w/ an apostrophe break this
		  $suffixRegex =			"/,* *($suffixes)$/";
		  $lastRegex =				"/(?!^)\b([^ ]+ y |$prefixes)*[^ ]+$/";
		  $leadingInitRegex =	"/^(.\.*)(?= \p{L}{2})/"; // note the lookahead, which isn't returned or replaced
		  $firstRegex =			"/^[^ ]+/"; //

		  // get suffix, if there is one
		  $this->title = $this->name->chopWithRegex($titleRegex, 1);

		  // get nickname, if there is one
		  $this->nicknames = $this->name->chopWithRegex($nicknamesRegex, 2);

		  // get suffix, if there is one
		  $this->suffix = $this->name->chopWithRegex($suffixRegex, 1);

		  // flip the before-comma and after-comma parts of the name
		  $this->name->flip(",");

		  // get the last name
		  $this->last = $this->name->chopWithRegex($lastRegex, 0);
		  if (!$this->last){
			  throw new Exception("Couldn't find a last name in '{$this->name->getStr()}'.");
		  }

		  // get the first initial, if there is one
		  $this->leadingInit = $this->name->chopWithRegex($leadingInitRegex, 1);

		  // get the first name
		  $this->first = $this->name->chopWithRegex($firstRegex, 0);
		  if (!$this->first){
			  throw new Exception("Couldn't find a first name in '{$this->name->getStr()}'");
		  }

		  // if anything's left, that's the middle name
		  $this->middle = $this->name->getStr();
		  return true;
	  }
}
?>
