/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    MediaUpload,
    MediaUploadCheck,
    InspectorControls,
} from '@wordpress/block-editor';
import {
    Button,
    PanelBody,
    RangeControl,
    Placeholder,
    Modal,
    TextControl,
} from '@wordpress/components';
import { Fragment, useState } from '@wordpress/element';
import { trash, search, arrowUp, arrowDown, dragHandle } from '@wordpress/icons';

/**
 * DnD Kit dependencies
 */
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    useSortable,
    rectSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

/**
 * Sortable Page Item Component
 */
function SortablePageItem({ page, index, onPreview, onRemove, onMoveUp, onMoveDown, isFirst, isLast }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: page.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
        zIndex: isDragging ? 1000 : 1,
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={`book-viewer-editor__page ${page.isBlank ? 'book-viewer-editor__page--blank' : ''} ${isDragging ? 'book-viewer-editor__page--dragging' : ''}`}
        >
            <div
                className="book-viewer-editor__drag-handle"
                {...attributes}
                {...listeners}
            >
                <span className="dashicons dashicons-move"></span>
            </div>
            <span className="book-viewer-editor__page-number">{index + 1}</span>
            {page.isBlank ? (
                <div className="book-viewer-editor__blank-page">
                    {__('Blank', 'book-viewer-block')}
                </div>
            ) : (
                <div
                    className="book-viewer-editor__image-wrapper"
                    onClick={() => onPreview(page, `${__('Page', 'book-viewer-block')} ${index + 1}`)}
                >
                    <img src={page.url} alt={page.alt} />
                    <Button
                        icon={search}
                        label={__('Preview full size', 'book-viewer-block')}
                        className="book-viewer-editor__preview-btn"
                    />
                </div>
            )}
            <div className="book-viewer-editor__page-actions">
                <Button
                    icon={arrowUp}
                    label={__('Move up', 'book-viewer-block')}
                    onClick={() => onMoveUp(index)}
                    disabled={isFirst}
                    size="small"
                />
                <Button
                    icon={arrowDown}
                    label={__('Move down', 'book-viewer-block')}
                    onClick={() => onMoveDown(index)}
                    disabled={isLast}
                    size="small"
                />
                <Button
                    icon={trash}
                    label={__('Remove', 'book-viewer-block')}
                    onClick={() => onRemove(index)}
                    isDestructive
                    size="small"
                />
            </div>
        </div>
    );
}

/**
 * Editor component for Book Viewer block.
 */
export default function Edit({ attributes, setAttributes }) {
    const { pages, frontCover, backCover, blankPages, pageWidth, pageHeight, paddingColor } = attributes;
    const blockProps = useBlockProps({
        className: 'book-viewer-editor',
    });

    // Preview modal state
    const [previewImage, setPreviewImage] = useState(null);
    const [previewTitle, setPreviewTitle] = useState('');

    // DnD Kit sensors
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    /**
     * Handle drag end - reorder pages
     */
    const handleDragEnd = (event) => {
        const { active, over } = event;

        if (active.id !== over?.id) {
            const oldIndex = pages.findIndex((p) => p.id === active.id);
            const newIndex = pages.findIndex((p) => p.id === over.id);
            const newPages = arrayMove(pages, oldIndex, newIndex);
            setAttributes({ pages: newPages });
        }
    };

    /**
     * Open preview modal for an image.
     */
    const openPreview = (image, title) => {
        if (image && image.url) {
            setPreviewImage(image);
            setPreviewTitle(title);
        }
    };

    /**
     * Close preview modal.
     */
    const closePreview = () => {
        setPreviewImage(null);
        setPreviewTitle('');
    };

    /**
     * Handle adding images to pages.
     */
    const onSelectPages = (media) => {
        const newPages = media.map((item) => ({
            id: item.id,
            url: item.url,
            alt: item.alt || '',
            width: item.width,
            height: item.height,
        }));
        setAttributes({ pages: newPages });
    };

    /**
     * Handle selecting front cover.
     */
    const onSelectFrontCover = (media) => {
        setAttributes({
            frontCover: {
                id: media.id,
                url: media.url,
                alt: media.alt || '',
                width: media.width,
                height: media.height,
            },
        });
    };

    /**
     * Handle selecting back cover.
     */
    const onSelectBackCover = (media) => {
        setAttributes({
            backCover: {
                id: media.id,
                url: media.url,
                alt: media.alt || '',
                width: media.width,
                height: media.height,
            },
        });
    };

    /**
     * Remove a page at the given index.
     */
    const removePage = (index) => {
        const newPages = [...pages];
        newPages.splice(index, 1);
        const newBlankPages = blankPages
            .filter((i) => i !== index)
            .map((i) => (i > index ? i - 1 : i));
        setAttributes({ pages: newPages, blankPages: newBlankPages });
    };

    /**
     * Move a page up in the order.
     */
    const movePageUp = (index) => {
        if (index === 0) return;
        const newPages = arrayMove(pages, index, index - 1);
        setAttributes({ pages: newPages });
    };

    /**
     * Move a page down in the order.
     */
    const movePageDown = (index) => {
        if (index === pages.length - 1) return;
        const newPages = arrayMove(pages, index, index + 1);
        setAttributes({ pages: newPages });
    };

    /**
     * Insert a blank page at position.
     */
    const insertBlankPage = (afterIndex) => {
        const newPages = [...pages];
        const blankPage = { id: `blank-${Date.now()}`, url: '', alt: 'Blank page', isBlank: true };
        newPages.splice(afterIndex + 1, 0, blankPage);
        setAttributes({ pages: newPages });
    };

    const hasContent = frontCover || backCover || pages.length > 0;

    return (
        <Fragment>
            {/* Preview Modal */}
            {previewImage && (
                <Modal
                    title={previewTitle}
                    onRequestClose={closePreview}
                    className="book-viewer-preview-modal"
                    isFullScreen
                >
                    <div className="book-viewer-preview-modal__content">
                        <img
                            src={previewImage.url}
                            alt={previewImage.alt || previewTitle}
                            style={{
                                maxWidth: '100%',
                                maxHeight: 'calc(100vh - 120px)',
                                objectFit: 'contain',
                                display: 'block',
                                margin: '0 auto',
                            }}
                        />
                    </div>
                </Modal>
            )}

            <InspectorControls>
                <PanelBody title={__('Book Settings', 'book-viewer-block')}>
                    <RangeControl
                        label={__('Page Width', 'book-viewer-block')}
                        value={pageWidth}
                        onChange={(value) => setAttributes({ pageWidth: value })}
                        min={200}
                        max={800}
                    />
                    <RangeControl
                        label={__('Page Height', 'book-viewer-block')}
                        value={pageHeight}
                        onChange={(value) => setAttributes({ pageHeight: value })}
                        min={200}
                        max={1200}
                    />
                    <TextControl
                        label={__('Padding/Border Color', 'book-viewer-block')}
                        help={__('CSS color value or variable, e.g. var(--yuki-base-color) or #333', 'book-viewer-block')}
                        value={paddingColor}
                        onChange={(value) => setAttributes({ paddingColor: value })}
                        placeholder="var(--yuki-base-color)"
                    />
                </PanelBody>

                <PanelBody title={__('Front Cover', 'book-viewer-block')} initialOpen={false}>
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={onSelectFrontCover}
                            allowedTypes={['image']}
                            value={frontCover?.id}
                            render={({ open }) => (
                                <div className="book-viewer-cover-control">
                                    {frontCover ? (
                                        <Fragment>
                                            <img
                                                src={frontCover.url}
                                                alt={frontCover.alt}
                                                style={{ maxWidth: '100%', marginBottom: '8px' }}
                                            />
                                            <Button variant="secondary" onClick={open}>
                                                {__('Replace', 'book-viewer-block')}
                                            </Button>
                                            <Button
                                                variant="tertiary"
                                                isDestructive
                                                onClick={() => setAttributes({ frontCover: null })}
                                            >
                                                {__('Remove', 'book-viewer-block')}
                                            </Button>
                                        </Fragment>
                                    ) : (
                                        <Button variant="secondary" onClick={open}>
                                            {__('Select Front Cover', 'book-viewer-block')}
                                        </Button>
                                    )}
                                </div>
                            )}
                        />
                    </MediaUploadCheck>
                </PanelBody>

                <PanelBody title={__('Back Cover', 'book-viewer-block')} initialOpen={false}>
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={onSelectBackCover}
                            allowedTypes={['image']}
                            value={backCover?.id}
                            render={({ open }) => (
                                <div className="book-viewer-cover-control">
                                    {backCover ? (
                                        <Fragment>
                                            <img
                                                src={backCover.url}
                                                alt={backCover.alt}
                                                style={{ maxWidth: '100%', marginBottom: '8px' }}
                                            />
                                            <Button variant="secondary" onClick={open}>
                                                {__('Replace', 'book-viewer-block')}
                                            </Button>
                                            <Button
                                                variant="tertiary"
                                                isDestructive
                                                onClick={() => setAttributes({ backCover: null })}
                                            >
                                                {__('Remove', 'book-viewer-block')}
                                            </Button>
                                        </Fragment>
                                    ) : (
                                        <Button variant="secondary" onClick={open}>
                                            {__('Select Back Cover', 'book-viewer-block')}
                                        </Button>
                                    )}
                                </div>
                            )}
                        />
                    </MediaUploadCheck>
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                {!hasContent ? (
                    <Placeholder
                        icon="book"
                        label={__('Book Viewer', 'book-viewer-block')}
                        instructions={__('Upload or select images to create a flipable book.', 'book-viewer-block')}
                    >
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={onSelectPages}
                                allowedTypes={['image']}
                                multiple
                                gallery
                                render={({ open }) => (
                                    <Button variant="primary" onClick={open}>
                                        {__('Select Pages', 'book-viewer-block')}
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                    </Placeholder>
                ) : (
                    <div className="book-viewer-editor__preview">
                        {/* Covers row */}
                        <div className="book-viewer-editor__covers">
                            <div className="book-viewer-editor__cover book-viewer-editor__cover--front">
                                <span className="book-viewer-editor__label">{__('Front Cover', 'book-viewer-block')}</span>
                                {frontCover ? (
                                    <div className="book-viewer-editor__image-wrapper" onClick={() => openPreview(frontCover, __('Front Cover', 'book-viewer-block'))}>
                                        <img src={frontCover.url} alt={frontCover.alt} />
                                        <Button
                                            icon={search}
                                            label={__('Preview full size', 'book-viewer-block')}
                                            className="book-viewer-editor__preview-btn"
                                        />
                                    </div>
                                ) : (
                                    <div className="book-viewer-editor__placeholder">
                                        {__('No front cover', 'book-viewer-block')}
                                    </div>
                                )}
                            </div>
                            <div className="book-viewer-editor__cover book-viewer-editor__cover--back">
                                <span className="book-viewer-editor__label">{__('Back Cover', 'book-viewer-block')}</span>
                                {backCover ? (
                                    <div className="book-viewer-editor__image-wrapper" onClick={() => openPreview(backCover, __('Back Cover', 'book-viewer-block'))}>
                                        <img src={backCover.url} alt={backCover.alt} />
                                        <Button
                                            icon={search}
                                            label={__('Preview full size', 'book-viewer-block')}
                                            className="book-viewer-editor__preview-btn"
                                        />
                                    </div>
                                ) : (
                                    <div className="book-viewer-editor__placeholder">
                                        {__('No back cover', 'book-viewer-block')}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Pages grid with drag and drop */}
                        <div className="book-viewer-editor__pages">
                            <div className="book-viewer-editor__pages-header">
                                <span>{__('Pages', 'book-viewer-block')} ({pages.length})</span>
                                <MediaUploadCheck>
                                    <MediaUpload
                                        onSelect={onSelectPages}
                                        allowedTypes={['image']}
                                        multiple
                                        gallery
                                        value={pages.map((p) => p.id).filter((id) => typeof id === 'number')}
                                        render={({ open }) => (
                                            <Button variant="secondary" size="small" onClick={open}>
                                                {__('Edit Pages', 'book-viewer-block')}
                                            </Button>
                                        )}
                                    />
                                </MediaUploadCheck>
                                <Button
                                    variant="secondary"
                                    size="small"
                                    onClick={() => insertBlankPage(pages.length - 1)}
                                >
                                    {__('Add Blank Page', 'book-viewer-block')}
                                </Button>
                            </div>

                            <DndContext
                                sensors={sensors}
                                collisionDetection={closestCenter}
                                onDragEnd={handleDragEnd}
                            >
                                <SortableContext
                                    items={pages.map((p) => p.id)}
                                    strategy={rectSortingStrategy}
                                >
                                    <div className="book-viewer-editor__pages-grid">
                                        {pages.map((page, index) => (
                                            <SortablePageItem
                                                key={page.id}
                                                page={page}
                                                index={index}
                                                onPreview={openPreview}
                                                onRemove={removePage}
                                                onMoveUp={movePageUp}
                                                onMoveDown={movePageDown}
                                                isFirst={index === 0}
                                                isLast={index === pages.length - 1}
                                            />
                                        ))}
                                    </div>
                                </SortableContext>
                            </DndContext>
                        </div>
                    </div>
                )}
            </div>
        </Fragment>
    );
}
