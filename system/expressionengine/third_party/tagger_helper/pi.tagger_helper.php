<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// extend the core channel class
require_once PATH_MOD.'channel/mod.channel.php';

$plugin_info = array('pi_name' => 'Taecho Tagger Helper',
                     'pi_version' => '1.0',
                     'pi_author' => 'Robert Banh',
                     'pi_author_url' => 'http://www.taechogroup.com/',
                     'pi_description' => 'Custom plugin for Taecho Group, in use with DevDemon Tagger and Channel Files'
                     );

class Tagger_Helper extends Channel {  

  var $return_data = '';

  // Constructor
  //
  function Tagger_Helper() 
  {

    $this->EE =& get_instance();
    
        parent::Channel();
        $this->_params();

   }

  /** ----------------------------------------
    /**  Setup default parameters
    /** ----------------------------------------*/    
    private function _params()
    {
        $this->channel          = $this->EE->TMPL->fetch_param('channel');
        $this->url_title        = $this->EE->TMPL->fetch_param('url_title');
        $this->status           = $this->EE->TMPL->fetch_param('status', 'open');
        $this->category_group   = $this->EE->TMPL->fetch_param('category_group');
        $this->category         = $this->EE->TMPL->fetch_param('category');
        $this->group_id         = $this->EE->TMPL->fetch_param('group_id');
        $this->limit            = $this->EE->TMPL->fetch_param('limit');
        $this->orderby          = $this->EE->TMPL->fetch_param('orderby');
        $this->sort             = $this->EE->TMPL->fetch_param('sort');
        $this->entry_id         = $this->EE->TMPL->fetch_param('entry_id');
    }
    
 
    // ----------------------------------------
    /* {exp:tagger_helper:entries
        tag="{segment_3}"
        limit="10"
        orderby="media_date"
        sort="desc"
        dynamic="no"
        disable="category_fields|member_data" 
        cache="no"
        paginate="bottom"
        }
    */
    // ----------------------------------------   
    public function entries()
    {
        $tag = $this->EE->TMPL->fetch_param('tag');

        // fix spacing
        $tag = str_replace('+', ' ', $tag);

        // Grab all entries with this tag
        $this->EE->db->select('tl.tag_id, tl.entry_id');
        $this->EE->db->from('exp_tagger_links tl');
        $this->EE->db->join('exp_tagger t', 't.tag_id = tl.tag_id', 'left');
        $this->EE->db->where('t.tag_name', $tag);
        $this->EE->db->where_in('tl.site_id' , $this->EE->TMPL->site_ids);
        $this->EE->db->where('tl.type', 1);
        $query = $this->EE->db->get();

        // Did we find anything
        if ($query->num_rows() == 0)
        {
            return "No Results Found.";
        }

        // Loop through the results
        $entry_ids = array();
        foreach ($query->result() as $row)
        {
            $entry_ids[] = $row->entry_id;
        }

        $this->EE->TMPL->tagparams['entry_id'] = implode('|', $entry_ids);

        return parent::entries();
    }

    // ----------------------------------------
    /* {exp:tagger_helper:total_channel_files entry_id="{segment_3}"}
            {if total_files > 10} 
                Do something 
            {/if}
        {/exp:tagger_helper:total_channel_files}
    */
    // ----------------------------------------   
    public function total_channel_files()
    {
        $entry_id = ($this->EE->TMPL->fetch_param('entry_id') != FALSE) ? $this->EE->TMPL->fetch_param('entry_id') : false;

        if (!$entry_id) return 0;

        $this->EE->db->select('count(*)');
        $this->EE->db->from('exp_channel_files');
        $this->EE->db->where('entry_id', $entry_id);
        $query = $this->EE->db->get();

        $re = $query->result_array();
        $query->free_result();

        if (empty($re) || $re == false)
            return false;

        $total = current($re[0]);

        $variables[] = array(
            'total_files' => $total
            );

        $output = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);

        return $output;
    }




}
