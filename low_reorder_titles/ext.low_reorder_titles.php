<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Low_reorder_titles_ext {

	/**
	 * Extension settings
	 *
	 * @access      public
	 * @var         array
	 */
	public $settings = array();

	/**
	 * Extension name
	 *
	 * @access      public
	 * @var         string
	 */
	public $name = 'Low Reorder Titles';

	/**
	 * Extension version
	 *
	 * @access      public
	 * @var         string
	 */
	public $version = '0.0.1';

	/**
	 * Extension description
	 *
	 * @access      public
	 * @var         string
	 */
	public $description = 'Low Reorder Titles';

	/**
	 * Do settings exist?
	 *
	 * @access      public
	 * @var         bool
	 */
	public $settings_exist = FALSE;

	/**
	 * Documentation link
	 *
	 * @access      public
	 * @var         string
	 */
	public $docs_url = '#';

	// --------------------------------------------------------------------

	/**
	 * EE Instance
	 *
	 * @access      private
	 * @var         object
	 */
	private $EE;

	/**
	 * Current class name
	 *
	 * @access      private
	 * @var         string
	 */
	private $class_name;

	/**
	 * Current site id
	 *
	 * @access      private
	 * @var         int
	 */
	private $site_id;

	/**
	 * Image field
	 *
	 * @access      private
	 * @var         int
	 */
	private $field = 'field_id_6';

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access      public
	 * @param       mixed     Array with settings or FALSE
	 * @return      null
	 */
	public function __construct($settings = array())
	{
		// Get global instance
		$this->EE =& get_instance();

		// Get site id
		$this->site_id = $this->EE->config->item('site_id');

		// Set Class name
		$this->class_name = ucfirst(get_class($this));

		// Set settings
		$this->settings = $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Change entries
	 *
	 * @access      public
	 * @param       mixed     Array with settings or FALSE
	 * @return      null
	 */
	public function low_reorder_show_entries($entries, $set)
	{
		// Get entry IDs for this set
		$entry_ids = array();

		foreach ($entries AS $row)
		{
			$entry_ids[] = $row['entry_id'];
		}

		// Get image info for these entries
		$images = array();
		$query = $this->EE->db->select(array('entry_id', $this->field))
		       ->from('channel_data')
		       ->where_in('entry_id', $entry_ids)
		       ->get();

		foreach ($query->result_array() AS $row)
		{
			$images[$row['entry_id']] = $row[$this->field];
		}

		// If there are images,
		if ($images)
		{
			// Load typo lib
			$this->EE->load->library('typography');

			// Set thumb template
			$tmpl = '<img style="max-width:75px" src="%s" alt="%s" />';

			// Replace regular title with thumb if it's there
			foreach ($entries AS &$row)
			{
				$eid = $row['entry_id'];

				if ( ! empty($images[$eid]))
				{
					$src = $this->EE->typography->parse_file_paths($images[$eid]);
					$row['title'] = sprintf($tmpl, $src, $row['title']);
				}
			}

		}

		return $entries;
	}

	// --------------------------------------------------------------------

	/**
	 * Activate extension
	 *
	 * @access      public
	 * @return      null
	 */
	public function activate_extension()
	{
		$this->EE->db->insert('extensions', array(
			'class'    => $this->class_name,
			'method'   => 'low_reorder_show_entries',
			'hook'     => 'low_reorder_show_entries',
			'priority' => 1,
			'version'  => $this->version,
			'enabled'  => 'y',
			'settings' => ''
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Update extension
	 *
	 * @access      public
	 * @param       string    Saved extension version
	 * @return      null
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		// init data array
		$data = array();

		// Add version to data array
		$data['version'] = $this->version;

		// Update records using data array
		$this->EE->db->where('class', $this->class_name);
		$this->EE->db->update('exp_extensions', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Disable extension
	 *
	 * @access      public
	 * @return      null
	 */
	public function disable_extension()
	{
		// Delete records
		$this->EE->db->where('class', $this->class_name);
		$this->EE->db->delete('exp_extensions');
	}

}