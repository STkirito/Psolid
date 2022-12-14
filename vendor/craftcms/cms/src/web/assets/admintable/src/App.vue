<template>
    <div :id="tableId" class="vue-admin-table" :class="{ 'vue-admin-table-padded': padded }">
        <div v-show="showToolbar" class="toolbar">
            <div class="flex">

                <div v-for="(action,index) in actions" :key="index">
                    <admin-table-action-button
                        :label="action.label"
                        :icon="action.icon"
                        :action="action.action"
                        :actions="action.actions"
                        :ids="checks"
                        :enabled="checks.length ? true : false"
                        v-on:reload="reload"
                    >
                    </admin-table-action-button>
                </div>

                <div v-if="search && !tableData.length" class="flex-grow texticon search icon clearable">
                    <input
                        class="text fullwidth"
                        type="text"
                        autocomplete="off"
                        :placeholder="searchPlaceholderText"
                        v-model="searchTerm"
                        @input="handleSearch"
                    >
                    <div class="clear hidden" :title="searchClearTitle"></div>
                </div>

            </div>
        </div>

        <div :class="{ 'content-pane': fullPage }">
            <div v-if="this.isEmpty" class="zilch">
                <p>{{ emptyMessage }}</p>
            </div>

            <div class="tableview" :class="{ loading: isLoading, hidden: this.isEmpty }">
                <div class="tablepane vue-admin-tablepane">
                    <vuetable
                            ref="vuetable"
                            :append-params="appendParams"
                            :api-mode="apiUrl ? true : false"
                            :api-url="apiUrl"
                            :css="tableCss"
                            :data="tableData"
                            :detail-row-component="detailRow"
                            :fields="fields"
                            :per-page="perPage"
                            pagination-path="pagination"
                            @vuetable:loaded="init"
                            @vuetable:loading="loading"
                            @vuetable:pagination-data="onPaginationData"
                    >
                        <template slot="checkbox" slot-scope="props">
                            <admin-table-checkbox
                                :id="props.rowData.id"
                                :checks="checks"
                                v-on:addCheck="addCheck"
                                v-on:removeCheck="removeCheck"
                            ></admin-table-checkbox>
                        </template>
                        <template slot="title" slot-scope="props">
                            <span v-if="props.rowData.status !== undefined" class="status" :class="{enabled: props.rowData.status}"></span>
                            <a class="cell-bold" v-if="props.rowData.url" :href="props.rowData.url">{{ props.rowData.title }}</a>
                            <span class="cell-bold" v-if="!props.rowData.url">{{ props.rowData.title }}</span>
                        </template>
                        <template slot="handle" slot-scope="props">
                            <code>{{ props.rowData.handle }}</code>
                        </template>
                        <template slot="menu" slot-scope="props">
                            <template v-if="props.rowData.menu.showItems">
                                <a :href="props.rowData.menu.url">{{props.rowData.menu.label}} ({{props.rowData.menu.items.length}})</a>
                                <a class="menubtn" :title="props.rowData.menu.label"></a>
                                <div class="menu">
                                    <ul>
                                        <li v-for="(item, index) in props.rowData.menu.items" :key="index">
                                            <a :href="item.url">{{item.label}}</a>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                            <template v-else>
                                <a :href="props.rowData.menu.url">{{props.rowData.menu.label}}</a>
                            </template>
                        </template>
                        <template slot="detail" slot-scope="props">
                            <div style="cursor: pointer;" @click="handleDetailRow(props.rowData.id)" v-if="props.rowData.detail.content" v-html="props.rowData.detail.handle"></div>
                        </template>
                        <template slot="reorder" slot-scope="props">
                            <i class="move icon vue-table-move-handle" :class="{disabled: !canReorder}" :data-id="props.rowData.id"></i>
                        </template>
                        <template slot="delete" slot-scope="props">
                            <admin-table-delete-button
                                :id="props.rowData.id"
                                :name="props.rowData.title"
                                :success-message="deleteSuccessMessage"
                                :confirmation-message="deleteConfirmationMessage"
                                :action-url="deleteAction"
                                :disabled="!canDelete"
                                v-on:reload="remove(props.rowIndex)"
                                v-if="props.rowData._showDelete == undefined || props.rowData._showDelete == true"
                            ></admin-table-delete-button>
                        </template>
                    </vuetable>
                </div>
                <admin-table-pagination
                        ref="pagination"
                        :itemLabels="itemLabels"
                        @vuetable-pagination:change-page="onChangePage"
                ></admin-table-pagination>
            </div>
        </div>

    </div>
</template>
<script>
    /* global Craft, Vue */
    import Vuetable from 'vuetable-2/src/components/Vuetable'
    import AdminTablePagination from './js/components/AdminTablePagination'
    import AdminTableDeleteButton from './js/components/AdminTableDeleteButton';
    import AdminTableCheckbox from './js/components/AdminTableCheckbox';
    import AdminTableActionButton from './js/components/AdminTableActionButton';
    import AdminTableDetailRow from './js/components/AdminTableDetailRow';
    import Sortable from 'sortablejs'
    import {debounce, map} from 'lodash'

    export default {
        components: {
            Vuetable,
            AdminTablePagination,
            AdminTableCheckbox,
            AdminTableDeleteButton,
            AdminTableActionButton
        },

        props: {
            container: {
                type: String,
            },
            actions: {
                type: Array,
                default: () => { return [] },
            },
            checkboxes: {
                type: Boolean,
                default: false,
            },
            columns: {
                type: Array,
                default: () => { return [] },
            },
            deleteAction: {
                type: String,
                default: null,
            },
            deleteCallback: {
                type: Function,
            },
            deleteConfirmationMessage: {
                type: String,
            },
            deleteSuccessMessage: {
                type: String
            },
            emptyMessage: {
                type: String,
                default: Craft.t('app', 'No data available.')
            },
            fullPage: {
                type: Boolean,
                default: false,
            },
            itemLabels: {
                type: Object,
                default: () => {
                    return {
                        singular: Craft.t('app', 'Item'),
                        plural: Craft.t('app', 'Items'),
                    }
                }
            },
            minItems: {
                type: Number
            },
            padded: {
                type: Boolean,
                default: false,
            },
            perPage: {
                type: Number,
                default: 40,
            },
            reorderAction: {
                type: String,
            },
            reorderSuccessMessage: {
                type: String,
                default: Craft.t('app', 'Items reordered.'),
            },
            reorderFailMessage: {
                type: String,
                default: Craft.t('app', 'Couldn???t reorder items.'),
            },
            search: {
                type: Boolean,
                default: false,
            },
            searchPlaceholder: {
                type: String,
                default: Craft.t('app', 'Search'),
            },
            tableData: {
                type: Array,
                default: () => { return [] },
            },
            tableDataEndpoint: {
                type: String,
            },
        },

        data() {
            return {
                checks: [],
                currentPage: 1,
                detailRow: AdminTableDetailRow,
                dragging: false,
                isEmpty: false,
                isLoading: true,
                searchClearTitle: Craft.escapeHtml(Craft.t('app', 'Clear')),
                searchTerm: null,
                selectAll: null,
                sortable: null,
                tableBodySelector: '.vuetable-body',
                tableClass: 'data fullwidth',
            }
        },

        methods: {
            init() {
                let tableBody = this.$el.querySelector(this.tableBodySelector);

                if (this.canReorder) {
                    this.sortable = Sortable.create(tableBody, {
                        animation: 150,
                        handle: '.move.icon',
                        ghostClass: 'vue-admin-table-drag',
                        onSort: this.handleReorder,
                        onStart: this.startReorder,
                        onEnd: this.endReorder,
                    })
                }
                this.isEmpty = (this.$refs.vuetable.tableData.length) ? false : true;

                this.$nextTick(() => {
                    if (this.$refs.vuetable) {
                        this.selectAll = this.$refs.vuetable.$el.querySelector('.selectallcontainer');
                        if (this.selectAll) {
                            this.selectAll.addEventListener('click', this.handleSelectAll);
                        }
                    }
                });

                this.isLoading = false;
            },

            loading() {
              this.isLoading = true;
            },

            startReorder() {
                this.dragging = true;
            },

            endReorder() {
                this.dragging = false;
            },

            handleReorder(ev) {
                let elements = [...ev.target.querySelectorAll('.vue-table-move-handle')];

                if (elements.length) {
                    let ids = map(elements, element => {
                        return element.dataset.id;
                    });

                    let data = {
                        ids: JSON.stringify(ids),
                        startPosition: (this.currentPage > 1 ? (this.currentPage-1) * this.perPage : 0) +1
                    };

                    Craft.postActionRequest(this.reorderAction, data, response => {
                        if (response && response.success) {
                            Craft.cp.displayNotice(Craft.escapeHtml(this.reorderSuccessMessage));
                        }
                    });
                } else {
                    Craft.cp.displayError(Craft.escapeHtml(this.reorderFailMessage));
                }
            },

            addCheck(id) {
                if (this.checks.indexOf(id) === -1) {
                    this.checks.push(id);
                }
            },

            removeCheck(id) {
                let key = this.checks.indexOf(id);
                if (key >= 0) {
                    this.checks.splice(key, 1);
                }
            },

            handleSearch: debounce(function() {
                if (this.$refs.vuetable) {
                    this.$refs.vuetable.gotoPage(1);
                }

                this.reload();
            }, 350),

            handleSelectAll() {
                var tableData = this.$refs.vuetable.tableData;
                if (this.checks.length != tableData.length) {
                    tableData.forEach(row => {
                        this.addCheck(row.id);
                    });
                } else {
                    this.checks = [];
                }
            },

            handleDetailRow(id) {
                this.$refs.vuetable.toggleDetailRow(id);
            },

            deselectAll() {
                this.checks = [];
            },

            reload() {
                this.isLoading = true;
                this.deselectAll();
                this.$refs.vuetable.reload();
            },

            remove(index) {
              this.isLoading = true;

              if (this.apiUrl) {
                  this.deselectAll();
                  this.$refs.vuetable.reload();
              } else {
                  Vue.delete(this.$refs.vuetable.tableData, index);
                  this.$refs.vuetable.refresh();
              }

              if (this.deleteCallback && {}.toString.call(this.deleteCallback) === '[object Function]') {
                  this.deleteCallback();
              }

              this.isLoading = false;
            },

            onPaginationData (paginationData) {
                this.currentPage = paginationData.current_page;
                this.$refs.pagination.setPaginationData(paginationData)
                this.deselectAll();
            },

            onChangePage (page) {
                this.$refs.vuetable.changePage(page)
                this.deselectAll();
            },
        },

        computed: {
            tableId() {
                // Replace either `#` or `.` from the container selector
                if (this.container) {
                    return this.container.replace(/[#.]/g,'');
                }
                
                return '';
            },
            
            apiUrl() {
                if (!this.tableDataEndpoint) {
                    return '';
                }

                return Craft.getActionUrl(this.tableDataEndpoint);
            },

            appendParams() {
                if (!this.searchTerm) {
                    return {};
                }

                return {
                    search: this.searchTerm
                };
            },

            canDelete() {
                return !(this.minItems && this.$refs.vuetable.tableData.length <= this.minItems)
            },

            canReorder() {
                return (this.$refs.vuetable.tableData.length > 1 && this.reorderAction && this.$el.querySelector(this.tableBodySelector) && (!this.$refs.vuetable.tablePagination))
            },

            fields() {
                let columns = [];

                // Enable/Disable checkboxes
                if (this.showCheckboxes) {
                    columns.push({
                        name: '__slot:checkbox',
                        titleClass: 'thin',
                        title: '<div class="checkbox-cell selectallcontainer" role="checkbox" tabindex="0" aria-checked="false"><div class="checkbox"></div></div>',
                        dataClass: 'checkbox-cell'
                    });
                }

                let customColumns = map(this.columns, item => {
                    // Do not allow sorting for if you can manually reorder items
                    if (this.reorderAction && item.hasOwnProperty('sortField')) {
                        delete item.sortField;
                    }

                    // Escape Title
                    item.title = Craft.escapeHtml(item.title);

                    return item;
                });

                columns = [...columns,...customColumns];

                if (this.reorderAction) {
                    columns.push({
                        name: '__slot:reorder',
                        title: '',
                        titleClass: 'thin'
                    });
                }

                if (this.deleteAction) {
                    columns.push({
                        name: '__slot:delete',
                        titleClass: 'thin'
                    });
                }

                return columns;
            },

            searchPlaceholderText() {
              return Craft.escapeHtml(this.searchPlaceholder);
            },

            showCheckboxes() {
              return (this.actions.length && this.checkboxes);
            },

            showToolbar() {
                return (this.actions.length || (this.search && !this.tableData.length));
            },

            tableCss() {
                var tableClass = this.tableClass;
                if (this.dragging) {
                    tableClass = tableClass + ' vue-admin-table-dragging';
                }

                return {
                    ascendingClass: 'ordered asc',
                    descendingClass: 'ordered desc',
                    sortableIcon: 'orderable',
                    handleIcon: 'move icon',
                    loadingClass: 'loading',
                    tableClass: tableClass,
                }
            }
        },

        watch: {
            checks() {
                if (this.selectAll) {
                    let checkbox = this.selectAll.querySelector('.checkbox');

                    if (this.checks.length && this.checks.length == this.$refs.vuetable.tableData.length) {
                        checkbox.classList.add('checked');
                        checkbox.classList.remove('indeterminate');
                    } else if (this.checks.length && this.checks.length != this.$refs.vuetable.tableData.length) {
                        checkbox.classList.remove('checked');
                        checkbox.classList.add('indeterminate');
                    } else {
                        checkbox.classList.remove('checked');
                        checkbox.classList.remove('indeterminate');
                    }
                }
            }
        }
    }
</script>

<style lang="scss">
    .tableview td.checkbox-cell {
        padding-right: 7px;
        width: 12px !important;
        position: relative;
    }

    .tableview td.checkbox-cell .checkbox {
        position: absolute;
        top: calc(50% - 6px);
    }

    .tableview.loading {
        opacity: .3;
    }

    .tableview .cell-bold {
        font-weight: bold;
    }

    .vue-admin-table .toolbar {
        margin-bottom: 32px;
    }

    .vue-admin-table.vue-admin-table-padded .toolbar {
        margin-bottom: 14px;
    }

    .vue-admin-table-padded .tablepane {
        margin: 0;
    }

    .vue-admin-table-drag {
        background: #f3f7fc;
    }

    table thead th.sortable:hover {
        background-color: #f9f9f9;
    }


    table.data.vue-admin-table-dragging tbody tr:not(.disabled):hover td {
        background-color: transparent;
    }

</style>
