import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import edit from './edit';
import save from './save';

// Import styles
import './style.scss';

registerBlockType(metadata.name, {
	edit,
	save,
});
