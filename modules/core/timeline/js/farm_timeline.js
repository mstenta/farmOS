(function (Drupal, drupalSettings, once, farmOS) {
  Drupal.behaviors.farm_timeline = {
    attach: function (context, settings) {
      once('timelineGantt', '.farm-timeline', context).forEach(function (element) {
        const opts = {
          props: {
            taskElementHook: (node, task) => {
              let popup;

              function onHover() {
                popup = createPopup(task, node);
              }

              function onLeave() {
                if (popup) {
                  popup.remove();
                }
              }

              node.addEventListener('mouseenter', onHover);
              node.addEventListener('mouseleave', onLeave);
              return {
                destroy() {
                  node.removeEventListener('mouseenter', onHover);
                  node.removeEventListener('mouseleave', onLeave);
                }
              }
            }
          },
        };

        // Create the timeline instance.
        const timeline = farmOS.timeline.create(element, opts);

        // Helper function to process a row data object and
        // add the row and its tasks to the timeline.
        const processRowData = function(row) {
          // Map to a row object.
          let mappedRow = Drupal.behaviors.farm_timeline.mapRow(row);
          timeline.addRows([mappedRow]);

          // Collect all tasks for the row.
          let tasks = row?.tasks?.map(Drupal.behaviors.farm_timeline.mapTask) ?? [];
          timeline.addTasks(tasks);

          // Process children rows.
          row?.children?.forEach(processRow) ?? [];
        };

        // Helper function to process a row provided to the timeline element.
        // Rows may be objects or URL strings to request dynamic row data.
        const processRow = function(row) {
          if (typeof row === "object") {
            processRowData(row);
          }
          else if (typeof row === "string") {
            const response = fetch(row)
              .then(res => res.json())
              .then(data => data.rows ?? [])
              .then(rows => rows.forEach(processRowData));
          }
        };

        // Process timeline rows.
        const timelineRows = JSON.parse(element.dataset?.timelineRows) ?? [];
        timelineRows.forEach(processRow);

        function createPopup(task, node) {
          const rect = node.getBoundingClientRect();
          const div = document.createElement('div');
          div.className = 'sg-popup';
          div.innerHTML = `
            <div class="sg-popup-title">${task.label}</div>
            <div class="sg-popup-item">From: ${new Date(task.from).toLocaleDateString()}</div>
            <div class="sg-popup-item">To: ${new Date(task.to).toLocaleDateString()}</div>
        `;
          div.style.position = 'absolute';
          div.style.top = `${rect.bottom + window.scrollY + 5}px`;
          div.style.left = `${rect.left + rect.width / 2}px`;

          if (task?.meta?.entity_type === 'log') {
            div.innerHTML = `
            <div class="sg-popup-title">Log: ${task.meta.label}</div>
            <div class="sg-popup-item">Type: ${task.meta.entity_bundle}</div>
            <div class="sg-popup-item">Timestamp: ${new Date(task.from).toLocaleDateString()}</div>
        `;
          }

          if (task?.meta?.stage) {
            div.innerHTML = `
            <div class="sg-popup-title">Stage: ${task.meta.stage}</div>
            <div class="sg-popup-item">From: ${new Date(task.from).toLocaleDateString()}</div>
            <div class="sg-popup-item">To: ${new Date(task.to).toLocaleDateString()}</div>
        `;
          }

          document.body.appendChild(div);
          return div;
        }

        // Open entity page on click.
        timeline.timeline.api.tasks.on.select((task) => {
          task = task[0];
          if (task.model?.editUrl) {
            var ajaxSettings = {
              url: task.model.editUrl,
              dialogType: 'dialog',
              dialogRenderer: 'off_canvas',
            };
            var myAjaxObject = Drupal.ajax(ajaxSettings);
            myAjaxObject.execute();
          } else {
            let dialog = document.getElementById('drupal-off-canvas');
            if (dialog) {
              Drupal.dialog(dialog, {}).close();
            }
          }
        });
      });
    },
    // Helper function to map row properties.
    mapRow: function(row) {
      return {
        id: row.id,
        label: row.label,
        headerHtml: row.link,
        expanded: row.expanded,
      };
    },
    // Helper function to map task properties.
    mapTask: function(task) {
      return {
        id: task.id,
        resourceId: task.resource_id,
        from: task.start,
        to: task.end,
        label: task.label,
        editUrl: task.edit_url,
        enableDragging: task.enable_dragging,
        meta: task?.meta,
        classes: task.classes,
      };
    },
  };
}(Drupal, drupalSettings, once, farmOS));
