<?php  
if (!defined('BASEPATH')) exit('No direct script access allowed');
	/**
	 * Api文档生成插件
	 * @author ym
	 */
	define('doc_path', str_replace("\\", "/", realpath(dirname(__FILE__))));

	class Apidoc{

		private $annotationCache;
		private $dirs = array();
		private $data = array();
		private $output = array();

		function __construct() {
			$CI = & get_instance();
			$CI->load->config('apidocs');
			$this->config =  $CI->config->item('settings');
			$this->listdirs($this->config['build_path'], $this->config['level']);
			$this->template_path = doc_path . '/template/';
			$this->vender_path = doc_path . '/vender/';
			$this->getAllAnnotations();
		}

		function generateTemplate(){
			$templates = $this->getHomeTemplate();
			$templates_data = $this->generateHomeData();
			$this->output['header'] = strtr($templates['header'], $templates_data);
			$this->output['footer'] = strtr($templates['footer'], $templates_data);
			$this->output['content'] = '';
			foreach ($this->data as $group => $class) {
				foreach ($class as $className => $object) {
					$content[$group]['group'] = strtr($templates['content'], array(
						'{{group}}'		 => $object['comment']['comment']['group'][0]['name'],
						'{{group_desc}}' => $object['comment']['comment']['group'][0]['description'],
					)) . "\n";
					$this->class = $className;
					foreach ($object['methods'] as $method => $annotion) {
						$this->method = $method;
						$content[$group]['item'][] = strtr($templates['item'], array('{{ext}}' => $this->config['template_ext'], '{{class}}' => $className, '{{method}}' => $method, '{{description}}' => $annotion['comment'][$this->config['rule']['description']][0]['description'])) . "\n";
						$sub_data = $this->generateItemPage($annotion);
						$sub_file = $this->vender_path . "{$className}/{$method}{$this->config['template_ext']}";
						if (!is_dir($this->vender_path . $className)) mkdir($this->vender_path . $className);
						$this->saveTemplate($sub_file, $sub_data);
					}
				}
			}
			$this->generateContent($content);
			return $this->output;
		}

		function generateContent($data){
			$this->output['content'] = '';
			foreach($data as $group => $items){
				$this->output['content'] .= $items['group'];
				foreach($items['item'] as $item){
					$this->output['content'] .= $item;
				}
			}
			return $this->output['content'];
		}

		function getLineInfo($startLine, $endLine){
			$line = $endLine - $startLine;
			if($line <= 20) return "该方法占用{$line}行, 代码块优化的很好!";
			if($line > 20 && $line < 50) return "该方法占用{$line}行, 代码块优化的比较好!";
			return "您的代码块已经有{$line}行了, 请及时优化！";
		}

		function json_format_item($str){
			if(empty($str)) return false;
			$success = '';
			$success_obj = json_decode(str_replace("'", '"', $str), true);
			$i = 0;
			foreach($success_obj as $key => $item){
				if($i >= count($success_obj) -1){
					$success .= "{$key} : {$item},";
				}else{
					$success .= "{$key} : {$item}," . "\n\t";
				}
				$i++;
			}
			return $success;
		}

		function getOutputParams($template, $params){
			$format_data = '';
			foreach($params as $param){
				$data = array(
					'{{params}}'      => $param['name'],
					'{{is_selected}}' => isset($param['is_selected']) ? 'true' : 'false',
					'{{field_type}}'  => $param['type'],
					'{{field_desc}}'  => $param['description'],
				);
				$format_data .= strtr($template, $data) . "\n";
			}
			return $format_data;
		}

		function getSystemInfo($template, $annotion){
			$system = strtr($template, array(
				'{{method_attribute}}' => $annotion['method_attribute'][0],
				'{{file_name}}'      => $annotion['fileName'],
				'{{start_line}}'     => $annotion['startLine'],
				'{{end_line}}'       => $annotion['endLine'],
				'{{sys_info}}'       => $this->getLineInfo($annotion['startLine'], $annotion['endLine']),
			)) . "\n";
			return $system;
		}

		function generateItemPage($annotion){
			$CI = & get_instance();
			$CI->load->helper('url');
			$templates = $this->getSubPageTemplate();
			$comment = $annotion['comment'];
			$params = $comment[$this->config['rule']['params']];
			$return = $comment[$this->config['rule']['return']];
			$description = $comment[$this->config['rule']['description']][0];
			$access = isset($comment[$this->config['rule']['access']]) ? $comment[$this->config['rule']['access']][0]['login'] : false;
			$notice = isset($comment[$this->config['rule']['notice']]) ? $comment[$this->config['rule']['notice']][0]['description'] : '';
			$example_str = isset($comment[$this->config['rule']['example']]) ? $comment[$this->config['rule']['example']][0]['value'] : '';
			$success_str = isset($comment[$this->config['rule']['success']]) ? $comment[$this->config['rule']['success']][0]['value'] : '';
			$subpage = strtr($templates['subpage'], array(
				'{{section}}'  => $description['section'],
				'{{site_url}}' => site_url("{$this->class}/{$this->method}"),
				'{{format}}'   => $this->config['output_format'],
				'{{request_method}}' => $description['method'],
				'{{is_login}}' => $access ? 'true' : 'false',
				'{{request_format}}' => $this->getOutputParams($templates['request_format'], $params),
				'{{return_format}}' => $this->getOutputParams($templates['return_format'], $return),
				'{{notice}}' => $notice,
				'{{request_example}}' => $this->json_format_item($example_str),
				'{{return_data}}' => $this->json_format_item($success_str),
				'{{system}}' => $this->getSystemInfo($templates['system'], $annotion),

			));
			return $subpage;
		}

		function generateHomeData(){
			return array(
				'{{section}}' 	  => $this->config['title'],
				'{{logo}}'  	  => $this->config['logo'],
				'{{footer}}'	  => $this->config['footer'],
			);
		}

		function getHomeTemplate(){
			$ext = $this->config['template_ext'];
			return array(
				'header'  => file_get_contents($this->template_path. $this->config['template']. '/home/header' . $ext),
				'content' => file_get_contents($this->template_path. $this->config['template']. '/home/content' . $ext),
				'item'	  => file_get_contents($this->template_path. $this->config['template']. '/home/item' . $ext),
				'footer'  => file_get_contents($this->template_path. $this->config['template']. '/home/footer' . $ext),
				
			);
		}

		function getSubPageTemplate(){
			$ext = $this->config['template_ext'];
			return array(
				'subpage' 		 => file_get_contents($this->template_path. $this->config['template']. '/subpage/subpage' . $ext),
				'request_format' => file_get_contents($this->template_path. $this->config['template']. '/subpage/request_format' . $ext),
				'return_format'  => file_get_contents($this->template_path. $this->config['template']. '/subpage/return_format'. $ext),
				'system'  		 => file_get_contents($this->template_path. $this->config['template']. '/subpage/system' . $ext),
			);
		}

		function generateHomeTemplate(){
			$homeFile = $this->vender_path . 'README'. $this->config['template_ext'];
			$data = array(
				'header'  => $this->output['header'] . "\n",
				'content' => $this->output['content'],
				'footer'  => $this->output['footer'],
			);
			$this->saveTemplate($homeFile, $data);
		}

		function saveTemplate($file, $data){
			$handle=fopen($file, "w+");
			if(is_array($data)){
				foreach($data as $item){
					fwrite($handle, $item);
				}
			}else{
				fwrite($handle, $data);
			}
			fclose($handle);
		}

		function build_doc(){
			$this->generateTemplate();
			$this->generateHomeTemplate();
		}

		function getAllAnnotations(){
			foreach($this->dirs as $dir){
				$this->getAnnotations($dir);
			}
			$this->sort_doc();
			return $this->data;
		}

		function sort_doc(){
			foreach($this->annotationCache as $class => $annotation){
				if(isset($annotation['class']['comment']['group'])){
					$this->data[$annotation['class']['comment']['group'][0]['name']][$class] = array(
						'comment' => $annotation['class'],
						'methods' => $annotation['methods'],
					);
				}
			}
			return $this->data;
		}

		function getAnnotations($path){
			foreach(glob($path.$this->config['allowed_file'], GLOB_BRACE) as $filename){
				require_once $filename;
				$file = pathinfo($filename);
				$this->getAnnoation($file['filename']);
			}
			return $this->annotationCache;
		}

		function getAnnoation($className){
			if (!isset($this->annotationCache[$className])) {
				$class = new \ReflectionClass($className);
				$this->annotationCache[$className] = $this->getClassAnnotation($class);
				$this->getMethodAnnotations($class);
			}
			return $this->annotationCache;
		}

		function getMethodAnnotations($className)
		{
			foreach ($className->getMethods() as $object) {
				if($object->name == 'get_instance' || $object->name == $className->getConstructor()->name) continue;
				$method = new \ReflectionMethod($object->class, $object->name);
				$this->annotationCache[strtolower($object->class)]['methods'][$object->name] = $this->getMethodAnnotation($method);
			}
			return $this->annotationCache;
		}

		function getClassAnnotation($class){
			return array('class' => array(
				'comment' => self::parseAnnotations($class->getDocComment()),
				'parentClass' => $class->getParentClass()->name,
				'fileName'	=> $class->getFileName(),
				'startLine' => $class->getStartLine(),
				'endLine'	=> $class->getEndLine(),
			));
		}

		function getMethodAnnotation($method){
			return array(
				'comment' => self::parseAnnotations($method->getDocComment()),
				'startLine' => $method->getStartLine(),
				'endLine'	=> $method->getEndLine(),
				'fileName'	=> $method->getFileName(),
				'method_attribute' => \Reflection::getModifierNames($method->getModifiers()),
			);
		}

		/**
	     * Parse annotations
	     *
	     * @param  string $docblock
	     * @return array  parsed annotations params
	     */
		private static function parseAnnotations($docblock)
		{
			$annotations = array();

			// Strip away the docblock header and footer to ease parsing of one line annotations
			$docblock = substr($docblock, 3, -2);

			if (preg_match_all('/@(?<name>[A-Za-z_-]+)[\s\t]*\((?<args>.*)\)[\s\t]*\r?$/m', $docblock, $matches)) {
				$numMatches = count($matches[0]);

				for ($i = 0; $i < $numMatches; ++$i) {
				// annotations has arguments
					if (isset($matches['args'][$i])) {
						$argsParts = trim($matches['args'][$i]);
						$name      = $matches['name'][$i];
						$value     = self::parseArgs($argsParts);
					} else {
						$value = array();
					}

					$annotations[$name][] = $value;
				}
			}
			return $annotations;
		}

		/**
		 * Parse individual annotation arguments
		 *
		 * @param  string $content arguments string
		 * @return array  annotated arguments
		 */
		private static function parseArgs($content)
		{
			$data  = array();
			$len   = strlen($content);
			$i     = 0;
			$var   = '';
			$val   = '';
			$level = 1;

			$prevDelimiter = '';
			$nextDelimiter = '';
			$nextToken     = '';
			$composing     = false;
			$type          = 'plain';
			$delimiter     = null;
			$quoted        = false;
			$tokens        = array('"', '"', '{', '}', ',', '=');

			while ($i <= $len) {
				$c = substr($content, $i++, 1);

				//if ($c === '\'' || $c === '"') {
			    if ($c === '"') {
					$delimiter = $c;
					//open delimiter
					if (!$composing && empty($prevDelimiter) && empty($nextDelimiter)) {
						$prevDelimiter = $nextDelimiter = $delimiter;
						$val           = '';
						$composing     = true;
						$quoted        = true;
					} else {
						// close delimiter
						if ($c !== $nextDelimiter) {
							throw new Exception(sprintf(
								"Parse Error: enclosing error -> expected: [%s], given: [%s]",
								$nextDelimiter, $c
							));
						}

						// validating sintax
						if ($i < $len) {
							if (',' !== substr($content, $i, 1)) {
								throw new Exception(sprintf(
									"Parse Error: missing comma separator near: ...%s<--",
									substr($content, ($i-10), $i)
								));
							}
						}

						$prevDelimiter = $nextDelimiter = '';
						$composing     = false;
						$delimiter     = null;
					}
				} elseif (!$composing && in_array($c, $tokens)) {
					switch ($c) {
					    case '=':
							$prevDelimiter = $nextDelimiter = '';
							$level     = 2;
							$composing = false;
							$type      = 'assoc';
							$quoted = false;
							break;
						case ',':
							$level = 3;

							// If composing flag is true yet,
							// it means that the string was not enclosed, so it is parsing error.
							if ($composing === true && !empty($prevDelimiter) && !empty($nextDelimiter)) {
								throw new Exception(sprintf(
									"Parse Error: enclosing error -> expected: [%s], given: [%s]",
									$nextDelimiter, $c
								));
							}

							$prevDelimiter = $nextDelimiter = '';
							break;
					    case '{':
							$subc = '';
							$subComposing = true;

							while ($i <= $len) {
								$c = substr($content, $i++, 1);

								if (isset($delimiter) && $c === $delimiter) {
									throw new Exception(sprintf(
										"Parse Error: Composite variable is not enclosed correctly."
									));
								}

								if ($c === '}') {
									$subComposing = false;
									break;
								}
								$subc .= $c;
							}

							// if the string is composing yet means that the structure of var. never was enclosed with '}'
							if ($subComposing) {
							    throw new Exception(sprintf(
							        "Parse Error: Composite variable is not enclosed correctly. near: ...%s'",
							        $subc
							    ));
							}

							$val = self::parseArgs($subc);
							break;
					}
				} else {
					if ($level == 1) {
						$var .= $c;
					} elseif ($level == 2) {
						$val .= $c;
					}
				}

			    if ($level === 3 || $i === $len) {
					if ($type == 'plain' && $i === $len) {
						$data = self::castValue($var);
					} else {
						$data[trim($var)] = self::castValue($val, !$quoted);
					}

					$level = 1;
					$var   = $val = '';
					$composing = false;
					$quoted = false;
				}
			}

			return $data;
		}

		private static function castValue($val, $trim = false)
		{
			if (is_array($val)) {
				foreach ($val as $key => $value) {
					$val[$key] = self::castValue($value);
				}
			} elseif (is_string($val)) {
				if ($trim) {
					$val = trim($val);
				}

				$tmp = strtolower($val);

				if ($tmp === 'false' || $tmp === 'true') {
					$val = $tmp === 'true';
				} elseif (is_numeric($val)) {
					return $val + 0;
				}

				unset($tmp);
			}

			return $val;
		}

		function listdirs($path, $level='') {
			$filepath = "{$level}{$path}/*";
			$this->dirs[] = $filepath;
			$dirs = glob($filepath, GLOB_ONLYDIR);
			if(count($dirs) > 0){
				foreach ($dirs as $dir) $this->listdirs($dir);
			}
			return $this->dirs;
		}
	}
?>