<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\GeoDispersion\Views;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for AdminConfig@index
 */
class AdminConfigView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        
        /** @var Tree $tree  */
        $tree = $this->data->get('tree');
        $root_url = $this->data->get('root_url');
        $other_trees = $this->data->get('other_trees');
        $table_id = $this->data->get('table_id');
        ?>        
        <ol class="breadcrumb small">
        	<li><a href="admin.php"><?php echo I18N::translate('Control panel'); ?></a></li>
			<li><a href="admin_modules.php"><?php echo I18N::translate('Module administration'); ?></a></li>
			<li class="active"><?php echo $this->data->get('title'); ?></li>
		</ol>
		
		<h1><?php echo $this->data->get('title'); ?></h1>
		
		<h2>
			<?php echo $tree->getTitleHtml(); ?>
			<?php if(count($other_trees) > 0) {?>
			<div class="btn-group">
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
					<?php echo I18N::translate('Change tree'); ?>
					<span class="caret"></span>
				</button>
				<?php foreach($other_trees as $other_tree) { ?>
				<ul class="dropdown-menu" role="menu">
					<li>
						<a href="<?php echo $root_url . '&ged=' . $other_tree->getNameUrl(); ?>">
							<i class="fa fa-fw fa-tree"></i>&nbsp;<?php echo $other_tree->getTitleHtml(); ?>
						</a>
					</li>
    			</ul>
    			<?php } ?>
    		</div>
    		<?php } ?>
		</h2>
		
		<p>
		<?php $places_hierarchy = $this->data->get('places_hierarchy'); 
		if($places_hierarchy) {
		    switch ($places_hierarchy['type']) {
		        case 'header':
		            echo I18N::translate('According to the GEDCOM header, the places within your file follows the structure: ');
		            break;
		        case 'data':
		            echo I18N::translate('Your GEDCOM header does not contain any indication of place structure.').
		            '<br/>'.
		            I18N::translate('Here is an example of your place data: ');
		            break;
		        default:
		            break;
		    }
		    $str_hierarchy = array();
		    foreach($places_hierarchy['hierarchy'] as $key => $level) {
		        $str_hierarchy[] = I18N::translate('(%d) %s', $key + 1, $level);
		    }
		    echo '<strong>' . implode(I18N::$list_separator, $str_hierarchy) . '</strong>';
		}
		?>
		</p>

		<table id="<?php echo $table_id; ?>" class="table table-condensed table-bordered">
    		<thead>
    			<tr>
    				<th><?php echo I18N::translate('Edit'); ?></th>
    				<th><!-- geoanalysis id --></th>
    				<th><?php echo I18N::translate('Enabled'); ?></th>
    				<th><?php echo I18N::translate('Description'); ?></th>
    				<th><?php echo I18N::translate('Level of analysis'); ?></th>
    				<th><!-- Analysis Level -->
    				<th><?php echo I18N::translate('Map'); ?></th>
    				<th><?php echo I18N::translate('Map parent level'); ?></th>
    				<th><?php echo I18N::translate('Use flags'); ?></th>
    				<th><?php echo I18N::translate('Top places'); ?></th>
    			</tr>
    		</thead>
    		<tbody>
    		</tbody>
    	</table>
    	
        <a type="button" class="btn btn-primary" href="<?php echo $root_url . '@add&ged=' . $tree->getNameUrl(); ?>" title="<?php echo I18N::translate('Add'); ?>">
        	<i class="fa fa-plus"></i>
        	<?php echo I18N::translate('Add'); ?>
        </a>
		
		<?php        
    }
    
}
 