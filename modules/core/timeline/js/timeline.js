(function (Drupal, drupalSettings, once, farmOS) {
  Drupal.behaviors.farm_timeline_gantt = {
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

        const timeline = farmOS.timeline.create(element, opts);

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

        // Helper function to map row properties.
        const mapRow = function(row) {
          return {
            id: row.id,
            label: row.label,
            headerHtml: row.link,
            expanded: row.expanded,
          };
        }

        // Helper function to map task properties.
        const mapTask = function(task) {
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
        }

        // Helper function to process a row.
        // Collect tasks and child rows and child tasks.
        const processRow = function(row) {

          // Map to a row object.
          let mappedRow = mapRow(row);

          // Collect all tasks for the row.
          let tasks = row?.tasks?.map(mapTask) ?? [];

          // Process children rows.
          // Only create the children array if there are child rows.
          let processedChildren = row?.children?.map(processRow) ?? [];
          if (processedChildren.length) {
            mappedRow.children = [];
            processedChildren.forEach((child) => {
              mappedRow.children.push(child.row);
              tasks.push(...child.tasks)
            });
          }

          return {row: mappedRow, tasks};
        }

        // Build a URL to the plan timeline API.
        const url = new URL(element.dataset.timelineUrl, window.location.origin + drupalSettings.path.baseUrl);
        const response = fetch(url)
          .then(res => res.json())
          .then(data => {

            // Process each row.
            for (let i in data.rows) {
              const {row, tasks} = processRow(data.rows[i]);
              timeline.addRows([row]);
              timeline.addTasks(tasks);
            }
          });
      });
    },
  };
}(Drupal, drupalSettings, once, farmOS));
