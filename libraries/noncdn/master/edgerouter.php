<?php

namespace NonCDN;

/**
 * Master Edge Router class
 * This class handles routing a particular request either internally or to an edge.
 *
 * @package     NonCDN
 * @subpackage  Master
 */
class Master_EdgeRouter
{
	/**
	 * @var    Configuration  The configuration for this object.
	 * @since  1.0
	 */
	private $configuration;
	
	/**
	 * @var    Factory  The factory for this object.
	 * @since  1.0
	 */
	private $factory;
	
	/**
	 * Constructor
	 *
	 * @param   Configuration  $configuration  Configuration object for this instance.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function __construct(Configuration $configuration, $factory)
	{
		$this->configuration = $configuration;
		$this->factory = $factory;
	}
	
	/**
	 * Handle a request either directly or via an edge.
	 *
	 * @param   string  $username   The username of the requestor.
	 * @param   string  $container  The container being requested.
	 * @param   string  $path       The path to the file in the container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function handleRequest($username, $container, $path)
	{
		$edgeMap = $this->configuration->getEdgeMap();
		
		$addr = $_SERVER['REMOTE_ADDR'];
		$redirect = false;
		$edges = array();
		
		// check if we have an exact IP match; great for testing
		if (isset($edgeMap[$addr]))
		{
			$redirect = true;
			$edges = $edgeMap[$addr];
		}
		else
		{
			// look for CIDR formatted blocks
			foreach ($edgeMap as $address => $targetEdges)
			{
				// TODO: match remote_addr using CIDR
				//$redirect = true;
			}			
		}
		
		// if we're doing a redirect, lets handle that
		if ($redirect && count($edges))
		{
			$edge = $this->buildRoute($edges, $username, $container, $path);
			header('HTTP/1.1 303 Redirect to edge');
			header('Location: '. $edge);
			echo 'Redirecting to edge...<a href="' . $edge . '">' . $edge . '</a>';
			exit;
		}
		
		// so no redirect which means we just deliver locally
		$file = JPATH_ROOT.'/data/'. $container.'/'.$path;
		readfile($file);
	}
	
	/**
	 * Build a route for a user 
	 
	protected function buildRoute($edges, $username, $container, $path)
	{
		$edgeServers = $this->configuration->getEdgeServers();
		$edgeId = rand(0, count($edges) - 1);
		
		$token = $this->factory->buildTokenService()->generateToken($username, $edgeId);
		
		return $edges[$edgeId]."auth/$username/$token/$container/$path";
	}
}