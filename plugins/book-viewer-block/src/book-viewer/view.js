/**
 * Frontend view script for Book Viewer block.
 * Initializes StPageFlip when the book enters the viewport.
 */

/**
 * Create zoom modal for image viewing
 */
function createZoomModal() {
	if ( document.getElementById( 'book-viewer-zoom-modal' ) ) {
		return document.getElementById( 'book-viewer-zoom-modal' );
	}

	const modal = document.createElement( 'div' );
	modal.id = 'book-viewer-zoom-modal';
	modal.className = 'book-viewer-zoom-modal';
	modal.innerHTML = `
		<div class="book-viewer-zoom-modal__backdrop"></div>
		<div class="book-viewer-zoom-modal__content">
			<button class="book-viewer-zoom-modal__close" type="button" aria-label="Close">
				<svg viewBox="0 0 24 24" width="24" height="24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/></svg>
			</button>
			<img class="book-viewer-zoom-modal__image" src="" alt="" />
		</div>
	`;
	document.body.appendChild( modal );

	// Close on backdrop click
	modal.querySelector( '.book-viewer-zoom-modal__backdrop' ).addEventListener( 'click', () => {
		closeZoomModal();
	} );

	// Close on button click
	modal.querySelector( '.book-viewer-zoom-modal__close' ).addEventListener( 'click', () => {
		closeZoomModal();
	} );

	// Close on escape key
	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && modal.classList.contains( 'book-viewer-zoom-modal--open' ) ) {
			closeZoomModal();
		}
	} );

	return modal;
}

/**
 * Open zoom modal with image
 */
function openZoomModal( imgSrc, imgAlt ) {
	const modal = createZoomModal();
	const img = modal.querySelector( '.book-viewer-zoom-modal__image' );
	img.src = imgSrc;
	img.alt = imgAlt || '';
	
	
	modal.classList.add( 'book-viewer-zoom-modal--open' );
	document.body.style.overflow = 'hidden';
}

/**
 * Close zoom modal
 */
function closeZoomModal() {
	const modal = document.getElementById( 'book-viewer-zoom-modal' );
	if ( modal ) {
		modal.classList.remove( 'book-viewer-zoom-modal--open' );
		document.body.style.overflow = '';
	}
}

/**
 * Toggle fullscreen mode (CSS-based, not native Fullscreen API)
 */
function toggleFullscreen( container ) {
	container.classList.toggle( 'book-viewer--fullscreen' );
}

/**
 * Initialize a book viewer instance.
 *
 * @param {HTMLElement} container The book viewer container element.
 */
function initBookViewer( container ) {
	// Check if PageFlip is available
	if ( typeof window.St === 'undefined' || typeof window.St.PageFlip === 'undefined' ) {
		console.warn( 'Book Viewer: StPageFlip library not loaded.' );
		return;
	}

	const book = container.querySelector( '.book-viewer__book' );
	if ( ! book ) return;

	// Get page dimensions from data attributes
	const pageWidth = parseInt( container.dataset.pageWidth, 10 ) || 400;
	const pageHeight = parseInt( container.dataset.pageHeight, 10 ) || 600;

	// Collect all pages (covers + inner pages)
	const frontCover = book.querySelector( '.book-viewer__page--front' );
	const backCover = book.querySelector( '.book-viewer__page--back' );
	const innerPages = book.querySelectorAll( '.book-viewer__page--inner:not(.book-viewer__page--auto-blank)' );
	const autoBlank = book.querySelector( '.book-viewer__page--auto-blank' );

	// Determine if mobile (single page mode) - use 768px breakpoint
	const checkIfMobile = () => window.innerWidth < 768;
	let isMobile = checkIfMobile();

	// Build pages array in correct order
	const allPages = [];
	if ( frontCover ) allPages.push( frontCover );
	innerPages.forEach( ( page ) => allPages.push( page ) );

	// Only add auto-blank on desktop if inner pages are odd
	// Covers stand alone, only inner pages need to be even for 2-up spreads
	if ( ! isMobile ) {
		const innerPageCount = innerPages.length;
		const needsBlank = innerPageCount % 2 !== 0;

		if ( needsBlank ) {
			if ( autoBlank ) {
				allPages.push( autoBlank );
			} else {
				const blankPage = document.createElement( 'div' );
				blankPage.className = 'book-viewer__page book-viewer__page--inner book-viewer__page--blank book-viewer__page--auto-blank';
				blankPage.innerHTML = '<div class="book-viewer__blank"></div>';
				allPages.push( blankPage );
			}
		}
	}

	if ( backCover ) allPages.push( backCover );

	if ( allPages.length < 2 ) {
		return;
	}

	// Create toolbar (above the book)
	const toolbar = document.createElement( 'div' );
	toolbar.className = 'book-viewer__toolbar';
	toolbar.innerHTML = `
		<div class="book-viewer__toolbar-indicator">
			<span class="book-viewer__current-page">1</span>
			<span class="book-viewer__separator">/</span>
			<span class="book-viewer__total-pages">${ allPages.length }</span>
		</div>
		<button class="book-viewer__fullscreen-btn" type="button" aria-label="Toggle fullscreen">
			<svg class="book-viewer__fullscreen-icon--enter" viewBox="0 0 24 24" width="24" height="24">
				<path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" fill="currentColor"/>
			</svg>
			<svg class="book-viewer__fullscreen-icon--exit" viewBox="0 0 24 24" width="24" height="24">
				<path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z" fill="currentColor"/>
			</svg>
		</button>
	`;
	container.insertBefore( toolbar, book );

	// Create a container for the flipbook
	const flipContainer = document.createElement( 'div' );
	flipContainer.className = 'book-viewer__flipbook';
	book.appendChild( flipContainer );

	// Initialize PageFlip
	let pageFlip = new window.St.PageFlip( flipContainer, {
		width: pageWidth,
		height: pageHeight,
		size: 'stretch',
		minWidth: isMobile ? 280 : 200,
		maxWidth: isMobile ? 500 : 800,
		minHeight: 300,
		maxHeight: 1200,
		showCover: true,
		mobileScrollSupport: true,
		usePortrait: isMobile,
		startPage: 0,
		drawShadow: true,
		flippingTime: 800,
		useMouseEvents: true,
		swipeDistance: 30,
		clickEventForward: true,
		startZIndex: 0,
		autoSize: true,
		maxShadowOpacity: 0.5,
		showPageCorners: ! isMobile,
		disableFlipByClick: false,
	} );

	// Load pages into PageFlip with zoom buttons
	const pageElements = allPages.map( ( page, index ) => {
		const pageEl = document.createElement( 'div' );
		pageEl.className = 'book-viewer__flip-page';
		pageEl.dataset.pageIndex = index;

		// Clone the content
		const content = page.cloneNode( true );
		content.classList.remove( 'book-viewer__page', 'book-viewer__page--inner', 'book-viewer__page--cover', 'book-viewer__page--front', 'book-viewer__page--back', 'book-viewer__page--blank' );
		pageEl.appendChild( content );

		// Add zoom button if page has an image
		const img = content.querySelector( 'img' );
		if ( img && img.src ) {
			const zoomBtn = document.createElement( 'button' );
			zoomBtn.className = 'book-viewer__zoom-btn';
			zoomBtn.type = 'button';
			zoomBtn.setAttribute( 'aria-label', 'Zoom image' );
			zoomBtn.innerHTML = `
				<svg viewBox="0 0 24 24" width="24" height="24">
					<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" fill="currentColor"/>
					<path d="M12 10h-2v2H9v-2H7V9h2V7h1v2h2v1z" fill="currentColor"/>
				</svg>
			`;
			
			const imgSrc = img.src;
			const imgAlt = img.alt || '';
			
			// Direct handlers - stop ALL events
			const stopAll = ( e ) => {
				e.stopPropagation();
				e.stopImmediatePropagation();
				e.preventDefault();
				return false;
			};
			
			const doZoom = ( e ) => {
				stopAll( e );
				openZoomModal( imgSrc, imgAlt );
				return false;
			};
			
			zoomBtn.onmousedown = stopAll;
			zoomBtn.onmouseup = doZoom;
			zoomBtn.onclick = doZoom;
			zoomBtn.ontouchstart = stopAll;
			zoomBtn.ontouchend = doZoom;
			zoomBtn.onpointerdown = stopAll;
			zoomBtn.onpointerup = doZoom;
			
			pageEl.appendChild( zoomBtn );
		}

		return pageEl;
	} );

	pageFlip.loadFromHTML( pageElements );


	// Hide the fallback content
	const pagesContainer = book.querySelector( '.book-viewer__pages' );
	if ( frontCover ) frontCover.style.display = 'none';
	if ( backCover ) backCover.style.display = 'none';
	if ( pagesContainer ) pagesContainer.style.display = 'none';

	// Show navigation
	const nav = container.querySelector( '.book-viewer__nav' );
	if ( nav ) nav.removeAttribute( 'aria-hidden' );

	// Fullscreen button
	const fullscreenBtn = toolbar.querySelector( '.book-viewer__fullscreen-btn' );
	fullscreenBtn.addEventListener( 'click', () => {
		toggleFullscreen( container );
		// Give CSS time to apply, then update PageFlip size
		setTimeout( () => {
			pageFlip.update();
		}, 50 );
	} );


	// Navigation buttons
	const prevBtn = container.querySelector( '.book-viewer__nav-btn--prev' );
	const nextBtn = container.querySelector( '.book-viewer__nav-btn--next' );

	if ( prevBtn ) {
		prevBtn.addEventListener( 'click', () => {
			pageFlip.flipPrev();
		} );
	}

	if ( nextBtn ) {
		nextBtn.addEventListener( 'click', () => {
			pageFlip.flipNext();
		} );
	}

	// Update page indicator in toolbar
	const currentPageEl = toolbar.querySelector( '.book-viewer__current-page' );
	const updatePageIndicator = () => {
		if ( currentPageEl ) {
			currentPageEl.textContent = pageFlip.getCurrentPageIndex() + 1;
		}
	};

	pageFlip.on( 'flip', updatePageIndicator );
	pageFlip.on( 'changeState', updatePageIndicator );

	// Handle resize for responsive behavior
	let resizeTimeout;
	let currentIsMobile = isMobile;

	window.addEventListener( 'resize', () => {
		clearTimeout( resizeTimeout );
		resizeTimeout = setTimeout( () => {
			const nowMobile = checkIfMobile();
			if ( nowMobile !== currentIsMobile ) {
				currentIsMobile = nowMobile;
				const currentPage = pageFlip.getCurrentPageIndex();
				pageFlip.destroy();
				flipContainer.innerHTML = '';

				pageFlip = new window.St.PageFlip( flipContainer, {
					width: pageWidth,
					height: pageHeight,
					size: 'stretch',
					minWidth: nowMobile ? 280 : 200,
					maxWidth: nowMobile ? 500 : 800,
					minHeight: 300,
					maxHeight: 1200,
					showCover: true,
					mobileScrollSupport: true,
					usePortrait: nowMobile,
					startPage: currentPage,
					drawShadow: true,
					flippingTime: 800,
					useMouseEvents: true,
					swipeDistance: 30,
					clickEventForward: true,
					startZIndex: 0,
					autoSize: true,
					maxShadowOpacity: 0.5,
					showPageCorners: ! nowMobile,
				} );
				pageFlip.loadFromHTML( pageElements );
				pageFlip.on( 'flip', updatePageIndicator );
				pageFlip.on( 'changeState', updatePageIndicator );
			}
		}, 250 );
	} );

	// Mark as initialized
	container.classList.add( 'book-viewer--initialized' );
}

/**
 * Initialize all book viewers using IntersectionObserver for lazy loading.
 */
function initAllBookViewers() {
	const bookViewers = document.querySelectorAll( '.book-viewer:not(.book-viewer--initialized)' );

	if ( bookViewers.length === 0 ) return;

	// Use IntersectionObserver for lazy initialization
	if ( 'IntersectionObserver' in window ) {
		const observer = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						initBookViewer( entry.target );
						observer.unobserve( entry.target );
					}
				} );
			},
			{
				rootMargin: '200px 0px', // Increased for mobile
				threshold: 0,
			}
		);

		bookViewers.forEach( ( viewer ) => {
			observer.observe( viewer );
		} );

		// Fallback: if still not initialized after 3 seconds, force init
		setTimeout( () => {
			document.querySelectorAll( '.book-viewer:not(.book-viewer--initialized)' ).forEach( ( viewer ) => {
				observer.unobserve( viewer );
				initBookViewer( viewer );
			} );
		}, 3000 );
	} else {
		// No IntersectionObserver, init immediately
		bookViewers.forEach( initBookViewer );
	}
}

/**
 * Wait for StPageFlip to be available, then initialize.
 */
function waitForStPageFlip( callback, attempts = 0 ) {
	if ( typeof window.St !== 'undefined' && typeof window.St.PageFlip !== 'undefined' ) {
		callback();
	} else if ( attempts < 50 ) {
		// Retry up to 50 times (5 seconds total)
		setTimeout( () => waitForStPageFlip( callback, attempts + 1 ), 100 );
	} else {
		console.warn( 'Book Viewer: StPageFlip library failed to load after 5 seconds.' );
	}
}

// Initialize when DOM is ready and StPageFlip is available
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', () => {
		waitForStPageFlip( initAllBookViewers );
	} );
} else {
	waitForStPageFlip( initAllBookViewers );
}
