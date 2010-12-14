<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Highcharts {
	
	// Static var to increment the variable declaration in render function
	private static $chart_id = 1;
	
	private $shared_opts 	= array(); // shared grah data
	private $global_opts	= array(); // All stocked graph data
	private $opts 			= array(); // current graph data
	private $serie_index 	= 0;
	
	public $js_chart_name = 'chart'; // name of the chart
	
	public $js_chart_opts = 'charts_options'; // Name of the javascript options
	
	
	/**
	 * __construct function.
	 * You can pass a config array() to the constructor
	 * 
	 * @access public
	 * @param array $config. (default: array())
	 * @return void
	 */
	public function __construct($config = array())
	{		
		if (! empty($config)) $this->initialize($config);
		
		$this->opts['series'] = array();
		$this->opts['chart']['renderTo'] = 'hc_chart';

	}
	
	/**
	 * initialize function.
	 * load options form a config file or directelly from array.
	 * If 
	 * 
	 * @access public
	 * @param mixed $template
	 * @param bool $config_file. (default: false)
	 * @param string $config_path. (default: 'highcharts')
	 * @return void
	 */
	public function initialize($config = array(), $config_path = 'highcharts')
	{
		if (is_string($config) AND ! empty($config)) // string means "load this template"
		{
			$ci =& get_instance();
			$ci->config->load($config_path);
			
			$config = $ci->config->item($config);
			
		}
		if (isset($config['shared_options']) AND empty($this->shared_opts))
		{
			$this->shared_opts = $config['shared_options'];
		}
		//if (count($config) > 0) $this->opts = $this->set_local_options($config);
		return $this;
	}
	
	/**
	 * set_options function.
	 * This function can set global options shared by your charts
	 * 
	 * @access public
	 * @param array $options. (default: array())
	 * @return void
	 */
	public function set_global_options($options = array())
	{
		if (! empty($options)) $this->shared_opts = $this->set_local_options($options);
		
		return $this;
	}

		
	/**
	 * __call function.
	 * 
	 * @access public
	 * @param mixed $func
	 * @param mixed $args
	 * @return void
	 */
	public function __call($func, $args)
	{
		if (strpos($func,'_'))
		{
			list($action, $type) = explode('_', $func);
		
			if (! isset($this->opts[$type]))
			{
				$this->opts[$type] = array();
			}
			switch ($action)
			{
				case 'set':
				$this->opts[$type] = $this->set_local_options($args[0]);
				break;
				
				case 'push':
				$this->opts[$type] += $this->set_local_options($args[0]);
				break;
				
				case 'unset':
				$this->unset_local_options($args, $type);
				break;
			}
		}
		
		return $this;
	}
	
	/**
	 * set_options function.
	 * 
	 * @access private
	 * @param array $options. (default: array())
	 * @param array $root. (default: array())
	 * @return void
	 */
	private function set_local_options($options = array(), $root = array())
	{
		foreach ($options as $opt_key => $opt_name)
		{		
			if(is_string($opt_key))
			{
				if(is_object($opt_name))
				{
					$root[$opt_key] = array();
					$root[$opt_key] = $this->set_local_options($opt_name, $root[$opt_key]); // convert back to array
				}
				else $root[$opt_key] = $opt_name;
			}
		}
		return $root;
	}
	
	/**
	 * unset_options function.
	 * 
	 * @access private
	 * @param array $options. (default: array())
	 * @param mixed $type
	 * @return void
	 */
	private function unset_local_options($options = array(), $type)
	{		
		foreach ($options as $option)
		{
			if (array_key_exists($option, $this->opts[$type]))
			{
				unset($this->opts[$type][$option]);
			}
		}
	}
	
	// SHORTCUT FUNCTIONS
	// guessed most used parameters that desserves to be set quickly
	/**
	 * set_title function.
	 * 
	 * @access public
	 * @param string $title. (default: '')
	 * @param array $options. (default: array())
	 * @return void
	 */
	public function set_title($title = '', $options = array())
	{
		$this->opts['title']['text'] = $title;
		
		if (count($options) > 0) $this->opts['title'] += $options;

		return $this;
	}
	
	/**
	 * set_axis_titles function.
	 * quickly set x and y texts
	 * 
	 * @access public
	 * @param string $x_label. (default: '')
	 * @param string $y_label. (default: '')
	 * @return void
	 */
	function set_axis_titles($x_title = '', $y_title = '')
	{
		if ($x_title) $this->opts['xAxis']['title']['text'] = $x_title;
		if ($y_title) $this->opts['yAxis']['title']['text'] = $y_title;
		
		return $this;
	}
	
	/**
	 * render_to function.
	 * set the container's id to render the graph
	 * 
	 * @access public
	 * @param string $id. (default: '')
	 * @return void
	 */
	public function render_to($id = '')
	{
		$this->opts['chart']['renderTo'] = $id;

		return $this;
	}
	
	/**
	 * set_type function.
	 * The default series type for the chart
	 * 
	 * @access public
	 * @param string $type. (default: '')
	 * @return void
	 */
	public function set_type($type = '')
	{
		if ($type AND is_string($type)) $this->opts['chart']['type'] = $type;
		
		return $this;
	}
	
	/**
	 * set_dimensions function.
	 * fastly set dimension of the graph is desired
	 * 
	 * @access public
	 * @param mixed $width. (default: null)
	 * @param mixed $height. (default: null)
	 * @return void
	 */
	public function set_dimensions($width = null, $height = null)
	{
		if ($width)  $this->opts['chart']['width'] = (int)$width;
		if ($height) $this->opts['chart']['height'] = (int)$height;
		
		return $this;
	}
	
	/**
	 * set_serie function.
	 * 
	 * @access public
	 * @param string $s_serie_name. (default: '')
	 * @param array $a_value. (default: array())
	 * @return void
	 */
	public function set_serie($serie_name = '', $options = array())
	{
		
		if ($serie_name)
		{
			$index = $this->find_serie_name($serie_name);
			
			$this->opts['series'][$index] = array();
			
			if (count($options) > 0)
			{
				foreach($options as $key => $value)
				{
				    $value = (is_numeric($value)) ? (float)$value : $value;
				    $this->opts['series'][$index][$key] = $value;
				}
			}
		}
		return $this;
	}
	
	/**
	 * set_serie_option function.
	 * We are settings each serie options for graph
	 * 
	 * @access public
	 * @param string $s_serie_name. (default: '')
	 * @param string $s_option. (default: '')
	 * @param string $value. (default: '')
	 * @return void
	 */
	public function set_serie_options($serie_name = '', $options = array())
	{
		if ($serie_name AND count($options) > 0)
		{
			$index = $this->find_serie_name($serie_name);
						
			foreach ($options as $key => $opt)
			{
				$this->opts['series'][$index][$key] = $opt;
			}
		}
		return $this;
	}
	
	/**
	 * push_serie_data function.
	 * 
	 * @access public
	 * @param string $s_serie_name. (default: '')
	 * @param string $s_value. (default: '')
	 * @return void
	 */
	public function push_serie_data($serie_name = '', $value = ''){
		
		if ($serie_name AND $value)
		{
			$index = $this->find_serie_name($serie_name);
			
			$value = (is_numeric($value)) ? (float)$value : $value;
				
			$this->opts['series'][$index]['data'][] = $value;
		}
		return $this;
	}
	
	
	/**
	 * find_serie_name function.
	 * fonction qui permet de savoir si une série existe
	 * 
	 * @access private
	 * @return void
	 */
	private function find_serie_name($name)
	{
		$tot_indexes = count($this->opts['series']);
		
		if ($tot_indexes > 0)
		{
			foreach($this->opts['series'] as $index => $serie)
			{
				if (isset($serie['name']) AND strtolower($serie['name']) == strtolower($name))
				{
					return $index;
				}
			}
		}
		
		$this->opts['series'][$tot_indexes]['name'] = $name;
		
		return $tot_indexes;
	}

	
	/**
	 * push_categorie function.
	 * Add custom name to axes.
	 * 
	 * @access public
	 * @param mixed $value
	 * @return void
	 */
	public function push_categorie($value, $axis = 'x')
	{
		if(trim($value)!= '') $this->opts[$axis.'Axis']['categories'][] = $value;

		return $this;
	}	

	
	
	// AUTOMATIC DATABASE RENDERING
	/**
	 * from_result function.
	 * 
	 * @access public
	 * @param array $data. (default: array())
	 * @return void
	 */
	public function from_result($data = array())
	{
		if (! isset($this->opts['series']))
		{
			$this->opts['series'] = array();
		}
				
		foreach ($data['data'] as $row)
		{
			if (isset($data['x_label'])) $this->push_categorie($row->$data['x_label'],'x');
			if (isset($data['y_label'])) $this->push_categorie($row->$data['y_label'],'y');
			
			foreach ($data['series'] as $name => $value)
			{	
				// there is no options, juste assign name / value pair
				if (is_string($value))
				{
					$text = (is_string($name)) ? $name : $value;
					$dat  = $row->$value;
				}
				
				// options are passed
				else if (is_array($value))
				{
					if (isset($value['name']))
					{
						$text = $value['name'];
						unset($value['name']);
					}
					else
					{
						$text = $value['row'];
					}
					$dat = $row->{$value['row']};
					unset($value['row']);
					
					$this->set_serie_options($text, $value);
				}
				
				$this->push_serie_data($text, $dat);
			}
		}
		return $this;
	}
	
	
	
	/**
	 * add function.
	 * If options is a string, then the index of the current
	 * options to store is it
	 * 
	 * @access public
	 * @param array $options. (default: array())
	 * @return void
	 */
	function add($options = array(), $clear = true)
	{
		if (is_string($options) AND trim($options) !== '')
		{
			$this->global_opts[$options] = $this->opts;
		}
		else
		{
			$this->global_opts[self::$chart_id] = (count($options)> 0) ? $options : $this->opts;
		}
		
		self::$chart_id++;
		
		if ($clear === true) $this->clear();
		
		return $this;
	}
	

	/**
	 * get function.
	 * return the global options array as json string
	 * 
	 * @access public
	 * @return void
	 */
	public function get($clear = true)
	{
		if ((self::$chart_id -1) == count($this->global_opts) AND count($this->opts) > 0)
		{
			$this->add();
		}
		foreach ($this->global_opts as $key => $opts)
		{
			$this->global_opts[$key] = json_encode($opts);
		}	
		
		return $this->process_get($this->global_opts, $clear, 'json');
	}
	
	/**
	 * get_array function.
	 * return the raw options array
	 * 
	 * @access public
	 * @return void
	 */
	public function get_array($clear = true)
	{
		if ((self::$chart_id -1) == count($this->global_opts) AND count($this->opts) > 0)
		{
			$this->add();
		}
		
		return $this->process_get($this->global_opts, $clear, 'array');
	}
	
	/**
	 * process_get function.
	 * This functon send the output for get() and get_array().
	 * it will return an associative array if some global variables are defined.
	 * 
	 * @access private
	 * @param mixed $options
	 * @param mixed $clear
	 * @return Json / Array
	 */
	private function process_get($options, $clear, $type)
	{
		if (count($this->shared_opts) > 0)
		{
			$global = ($type == 'json') ? json_encode($this->shared_opts) : $this->shared_opts;
			
			$options = array('global' => $global, 'local' => $options);
		}
		
		if ($clear === true) $this->clear();
		
		return $options;
	}
	
	/**
	 * get_embed function.
	 * Return javascript embedable code and friend div
	 * 
	 * @access public
	 * @return void
	 */
	public function render($u_options = array())
	{
		$options = array('renderTo' => 'hc_chart');
		
		if (is_array($options) AND count($options > 0))
		{
			$options = $options + $u_options;
		}
		
		$i = 1; $d = 1; $divs = '';

		$embed  = '<script type="text/javascript">';
        $embed .= '$(document).ready(function(){';
       	       	       
        foreach ($this->global_opts as $opts)
        {
			if (count($this->shared_opts) > 0 AND $i === 1)
       		{
       			$embed .= 'Highcharts.setOptions('.json_encode($this->shared_opts).');';
       		}

        	if (($opts['chart']['renderTo'] == 'hc_chart'))
        	{
        		$opts['chart']['renderTo'] .= '_'.$d;
        		$d++;
        	}
			
			$embed .= 'var '.$this->js_chart_name.'_'.	$i.' = new Highcharts.Chart('.json_encode($opts).');';
			$divs  .= '<div id="'.$opts['chart']['renderTo'].'"></div>';
			$i++;
		}
        
        $embed .= '});';
        $embed .= '</script>';
        $embed .= $divs;
        
		self::$chart_id++;
        
        $this->clear();
                
        return $embed;
	}
	
	
	/**
	 * clear function.
	 * clear instance properties. Very general at the moment, should only reset
	 * desired vars when lib will be finish
	 * 
	 * @access public
	 * @return void
	 */
	public function clear($shared = false)
	{
		$this->opts = array();
		$this->opts['series'] = array();
		$this->opts['chart']['renderTo'] = 'hc_chart';
		$this->serie_index = 0;
		
		if ($shared === true) $this->shared_opts = array();
		
		return $this;
	}


}