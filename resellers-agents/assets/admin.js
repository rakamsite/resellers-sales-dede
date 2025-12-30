jQuery(function ($) {
  const container = $('[data-agents-container]');
  const template = document.getElementById('rs-agent-template');
  const toggle = $('[data-agents-toggle]');
  const fields = $('[data-agents-fields]');
  const addButton = $('[data-add-agent]');

  function setFieldsVisibility(enabled) {
    fields.toggle(enabled);
    addButton.toggle(enabled);
  }

  function bindItem(item) {
    item.find('[data-remove-agent]').on('click', function () {
      item.remove();
    });

    item.find('[data-upload-button]').on('click', function () {
      const frame = wp.media({
        title: 'انتخاب تصویر نماینده',
        button: { text: 'انتخاب' },
        multiple: false,
      });

      frame.on('select', function () {
        const attachment = frame.state().get('selection').first().toJSON();
        item.find('[data-image-id]').val(attachment.id);
        item
          .find('[data-image-preview]')
          .html(`<img src="${attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url}" alt="" />`);
      });

      frame.open();
    });

    item.find('[data-remove-image]').on('click', function () {
      item.find('[data-image-id]').val('0');
      item.find('[data-image-preview]').html('<span>بدون تصویر</span>');
    });
  }

  container.find('[data-agent-item]').each(function () {
    bindItem($(this));
  });

  toggle.on('change', function () {
    setFieldsVisibility($(this).is(':checked'));
  });

  setFieldsVisibility(toggle.is(':checked'));

  $('[data-add-agent]').on('click', function () {
    const index = container.find('[data-agent-item]').length;
    const html = template.innerHTML.replace(/__INDEX__/g, index);
    const item = $(html);
    container.append(item);
    bindItem(item);
  });
});
