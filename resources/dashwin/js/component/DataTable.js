import { DataTable } from "simple-datatables";

export default function DataTableElement(selector){
    let elm = document.querySelectorAll(selector);
      if( elm != 'undefined' && elm != null ){
        elm.forEach(item => {
          let showPerPage = item.dataset.showPerPage ? parseInt(item.dataset.showPerPage) : 10;
          let dataTables = new DataTable(item, {
            labels: {
                placeholder: "Type in to Search",
                searchTitle: "Search within table",
                pageTitle: "Page {page}",
                perPage: "Show",
                noRows: "No entries found",
                info: "{start} - {end} of {rows}",
                noResults: "No results match your search query",
            },
            perPage: showPerPage,
            prevText: `Prev`,
            nextText: `Next`,
            firstText: `First`,
            lastText: `Last`,
            template: (options, dom) => `<div class='${options.classes.top} flex items-center justify-between gap-x-3 !px-0 !pt-0 !pb-4 after:hidden'>
                ${
                options.searchable ?
                    `<div class='${options.classes.search}'>
                        <input class='${options.classes.input} block w-full box-border text-sm leading-4.5 px-4 py-1.5 h-9 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950  disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded transition-all' placeholder='${options.labels.placeholder}' type='search' title='${options.labels.searchTitle}'${dom.id ? ` aria-controls="${dom.id}"` : ""}>
                    </div>` :
                    ""
                }
                ${
                options.paging && options.perPageSelect ?
                    `<div class='${options.classes.dropdown}'>
                        <label class='inline-flex items-center gap-2'>
                          <span class='hidden sm:block'>${options.labels.perPage}</span><select class='${options.classes.selector} block w-full text-sm leading-4.5 !pe-10 !ps-3 py-1.5 h-9 min-w-[4.5rem] text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 focus:border-primary-500 outline-none focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded transition-all bg-[right_0.5rem_center] rtl:bg-[left_0.5rem_center]'></select> 
                        </label>
                    </div>` :
                    ""
                }
              </div>
              <div class='${options.classes.container} overflow-auto border !border-gray-200 dark:!border-gray-800 [&_tbody_tr:last-child_td]:border-b-0 [&_thead_tr_th]:border-b-gray-200 [&_thead_tr_th]:dark:border-b-gray-800 [&_td]:border-b [&_td]:border-b-gray-200 [&_td]:dark:border-b-gray-800 [&_th]:p-2 [&_thead_th_button]:leading-4 [&_thead_th_button]:whitespace-nowrap [&_thead_th]:!py-1 [&_.datatable-sorter]:pe-6 [&_.datatable-sorter]:after:-top-0.5 [&_.datatable-sorter]:before:bottom-px [&_td]:p-2 [&_th:first-child]:!ps-5 [&_th:last-child]:pe-5 [&_td:first-child]:ps-5 [& _td:last-child]:pe-5 rounded'${options.scrollY.length ? ` style='height: ${options.scrollY}; overflow-Y: auto;'` : ""}></div>
              <div class='${options.classes.bottom} !px-0 !pb-0 !pt-4 flex flex-col md:flex-row md:items-center gap-2 justify-between after:hidden'>
                <nav class='${options.classes.pagination}'></nav>
                  ${
                  options.paging ?
                      `<div class='${options.classes.info} text-xs text-slate-600'></div>` :
                      ""
              }
            </div>`
          });
          
          //scrolled rsponsive
          let dataContainer = item.dataset.nkContainer ? item.dataset.nkContainer : '',
          containerClass = dataContainer.split(' ');
          dataContainer && dataTables.container.classList.add(...containerClass)
        })
      }
}