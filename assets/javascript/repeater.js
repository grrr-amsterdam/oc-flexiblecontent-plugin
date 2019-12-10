/**
 * Tweaks to October's own Repeater field.
 *
 * - Will make sure Group Headers always have a header text. The accompanying CSS will make sure
 *   they're always visible.
 *
 *
 * Note: this file is loaded BEFORE the core backend formwidgets file, so we need to wrap al this in
 * $() to make sure it's executed at the end of the cycle.
 */
$(function() {
  // Save a reference to the Repeater prototype to piggy-back on its functions.
  if (!$.fn.fieldRepeater) {
    return;
  }
  const Repeater = $.fn.fieldRepeater.Constructor.prototype;

  const fixGroupTitle = $item => {
    if ($item.hasClass("placeholder")) {
      return;
    }
    const $repeaterRoot = $item.closest('[data-control="fieldrepeater"]');
    if (!$repeaterRoot.length) {
      return;
    }

    const repeaterInstance = $repeaterRoot.data("oc.repeater");
    if (!repeaterInstance || !Repeater.isPrototypeOf(repeaterInstance)) {
      return;
    }
    const title = Repeater.getCollapseTitle.call(repeaterInstance, $item);
    $item.find(".repeater-item-collapsed-title").text(title);
  };

  const fixDefaultValues = $item => {
    const $inputs = $item.find("[data-default-value]");
    $inputs.each(function() {
      const $input = $(this);
      if (!$input.val()) {
        $input.val($input.data("default-value"));
      }
    });
  };

  // Only act on repeaters belonging to FlexiblePages plugin
  const ROOT_ELEMENT = $("[data-flexible-content-container=1]");
  if (!ROOT_ELEMENT.length) {
    return;
  }

  // Make sure all items have a filled group title (October populates late, when collapsing the
  // first time 🙄
  ROOT_ELEMENT.find(".field-repeater-item").each(function() {
    const $item = $(this);
    fixGroupTitle($item);
  });

  // Listen for newly added list-items, and fix their titles as well.
  const observer = new MutationObserver(mutations => {
    mutations
      .filter(m => m.type === "childList")
      .map(m => Array.from(m.addedNodes).filter(n => n.nodeType === 1).forEach(x => {
          const $item = $(x);
          fixDefaultValues($item);
          fixGroupTitle($item);
        }));
  });

  const config = { attributes: false, childList: true, characterData: false };
  observer.observe(ROOT_ELEMENT.find(".field-repeater-items").get(0), config);
});
