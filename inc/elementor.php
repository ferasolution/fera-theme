<?php
if(!defined('ABSPATH'))exit;
abstract class FF_Elementor_Widget extends \Elementor\Widget_Base{
    protected const KIND = "opening";
    public function get_name(){return 'foundry-'.static::KIND;}
    public function get_title(){return 'Fera Foundry '.ucfirst(static::KIND);}
    public function get_icon(){return 'eicon-site-identity';}
    public function get_categories(){return ['general'];}
    protected function register_controls(){
        $this->start_controls_section('content',['label'=>'Fera Foundry content']);
        $fields=['opening'=>['preset'=>'home','background'=>'net','enabled'=>'yes','title'=>'Ideas become working systems.','tagline'=>'BUILD. CONNECT. EVOLVE.','status'=>'Ready to explore.','footer_label'=>'FERA SOLUTION'],'visual'=>['preset'=>'home','alt'=>'Titanium orbit and illuminated core','overline'=>'CONNECTED BY DESIGN','label_one'=>'SECURITY BY DESIGN','label_two'=>'BUILT FOR YOUR BUSINESS','caption'=>'BUILD / CONNECT / EVOLVE'],'inquiry'=>['heading'=>'Request a project quote','service'=>'Multiple services / Not sure yet','button_label'=>'Send project inquiry','message_placeholder'=>'Tell us about your project','phone_label'=>'Phone number'],'replay'=>['label'=>'Replay opening'],'showcase'=>['mode'=>'all','title'=>'Live demonstrations']];
        foreach($fields[static::KIND] as $key=>$default){$this->add_control($key,['label'=>ucwords(str_replace('_',' ',$key)),'type'=>\Elementor\Controls_Manager::TEXT,'default'=>$default]);}
        if(static::KIND==='visual')$this->add_control('image',['label'=>'Hero artwork','type'=>\Elementor\Controls_Manager::MEDIA,'default'=>['url'=>get_theme_file_uri('/assets/fera-orbit-v2.webp')]]);
        $this->end_controls_section();
    }
    protected function render(){if(static::KIND==='opening'&&\Elementor\Plugin::$instance->editor->is_edit_mode()){echo '<p>Fera Foundry opening animation — plays on the public page.</p>';return;}ff_component(static::KIND,$this->get_settings_for_display());}
}

class FF_Opening extends FF_Elementor_Widget{protected const KIND="opening";}
class FF_Visual extends FF_Elementor_Widget{protected const KIND="visual";}
class FF_Inquiry extends FF_Elementor_Widget{protected const KIND="inquiry";}
class FF_Replay extends FF_Elementor_Widget{protected const KIND="replay";}
class FF_Showcase extends FF_Elementor_Widget{protected const KIND="showcase";}
