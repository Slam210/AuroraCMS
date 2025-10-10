document.addEventListener( 'DOMContentLoaded', function() {
	// Prevent aggressive iframe caching in Firefox
	var statsIframe = document.getElementById( 'stats-iframe' );
	if ( statsIframe ) {
		statsIframe.contentWindow.location.href = statsIframe.src;
	}

	initCompatiblePluginsShowMoreToggle();
} );

/**
 * Initialize the show-more toggle for the Akismet compatible plugins list.
 *
 * Locates the plugins section, its list, and the show-more button; if any are missing, no action is taken.
 * When the button is clicked, toggles the list's `is-expanded` class, updates the button label from
 * `data-labelOpen`/`data-labelClosed`, sets `aria-expanded` accordingly, and if the list is collapsed while
 * the section is off-screen, scrolls the section into view at the start.
 */
function initCompatiblePluginsShowMoreToggle() {
  const section = document.querySelector( '.akismet-compatible-plugins' );
  const list = document.querySelector( '.akismet-compatible-plugins__list' );
  const button = document.querySelector( '.akismet-compatible-plugins__show-more' );

  if ( ! section || ! list || ! button ) {
  	return;
  }

  function isElementInViewport( element ) {
    const rect = element.getBoundingClientRect();
    return rect.top >= 0 && rect.bottom <= window.innerHeight;
  }

  function toggleCards() {
    list.classList.toggle( 'is-expanded' );
    const isExpanded = list.classList.contains( 'is-expanded' );
    button.textContent = isExpanded ? button.dataset.labelOpen : button.dataset.labelClosed;
    button.setAttribute( 'aria-expanded', isExpanded.toString() );
  
    if ( ! isExpanded && ! isElementInViewport( section ) ) {
      section.scrollIntoView( { block: 'start' } );
    }
  }

  button.addEventListener( 'click', toggleCards );
}