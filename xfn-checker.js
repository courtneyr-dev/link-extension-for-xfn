/**
 * XFN Link Checker
 *
 * Paste this into your browser console to find all XFN links on any page.
 * It will log detailed information about each link with XFN relationships.
 */

(function() {
  console.log('%c🔍 XFN Link Checker', 'color: #00a32a; font-size: 16px; font-weight: bold;');
  console.log('%c─────────────────────────────────────', 'color: #00a32a;');

  const xfnValues = [
    'contact', 'acquaintance', 'friend', 'met',
    'co-worker', 'colleague', 'co-resident', 'neighbor',
    'child', 'parent', 'sibling', 'spouse', 'kin',
    'muse', 'crush', 'date', 'sweetheart', 'me'
  ];

  const allLinks = document.querySelectorAll('a[rel]');
  let xfnCount = 0;
  let totalRelLinks = allLinks.length;

  console.log(`Found ${totalRelLinks} link(s) with rel attributes\n`);

  allLinks.forEach((link, index) => {
    const relValues = link.rel.split(' ').filter(Boolean);
    const xfnFound = relValues.filter(val => xfnValues.includes(val));
    const otherRel = relValues.filter(val => !xfnValues.includes(val));

    if (xfnFound.length > 0) {
      xfnCount++;
      console.log(`%c✓ XFN Link #${xfnCount}`, 'color: #00a32a; font-weight: bold;');
      console.log(`  Text: "${link.textContent.trim().substring(0, 50)}${link.textContent.trim().length > 50 ? '...' : ''}"`);
      console.log(`  URL: ${link.href}`);
      console.log(`  %cXFN: ${xfnFound.join(', ')}`, 'color: #4A90E2; font-weight: bold;');

      if (otherRel.length > 0) {
        console.log(`  Other rel: ${otherRel.join(', ')}`);
      }

      console.log(`  Element:`, link);
      console.log('');
    }
  });

  console.log('%c─────────────────────────────────────', 'color: #00a32a;');

  if (xfnCount > 0) {
    console.log(`%c✓ Success! Found ${xfnCount} link(s) with XFN relationships`, 'color: #00a32a; font-weight: bold;');
  } else {
    console.log('%c⚠ No XFN links found on this page', 'color: #f0b849; font-weight: bold;');
    console.log('Make sure:');
    console.log('  1. The page has been published (not just saved as draft)');
    console.log('  2. XFN relationships were added in the editor');
    console.log('  3. Inspector Controls are enabled in Settings → Link Extension for XFN');
  }

  console.log('%c─────────────────────────────────────', 'color: #00a32a;');

  // Return summary object
  return {
    totalLinks: allLinks.length,
    xfnLinks: xfnCount,
    links: Array.from(allLinks).filter(link => {
      const relValues = link.rel.split(' ').filter(Boolean);
      const xfnFound = relValues.filter(val => xfnValues.includes(val));
      return xfnFound.length > 0;
    }).map(link => ({
      text: link.textContent.trim(),
      url: link.href,
      rel: link.rel,
      xfn: link.rel.split(' ').filter(val => xfnValues.includes(val)),
      element: link
    }))
  };
})();
