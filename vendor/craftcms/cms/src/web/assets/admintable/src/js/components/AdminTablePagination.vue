<template>
  <div v-if="tablePagination" class="vue-admin-table-pagination flex pagination">
    <div
            @click="loadPage('prev')"
            class="page-link" data-icon="leftangle"
            :class="[isOnFirstPage ? 'disabled' : '']"
            title="Previous Page"></div>
    <div
            @click="loadPage('next')"
            class="page-link" data-icon="rightangle"
            :class="[isOnLastPage ? 'disabled' : '']"
            title="Next Page"></div>
    <div v-show="tablePagination" class="page-info">{{tablePagination.from}}-{{tablePagination.to}} of {{tablePagination.total}} {{countWording}}</div>
  </div>
</template>

<script>
    /* global Craft */
    import PaginationMixin from 'vuetable-2/src/components/VuetablePaginationMixin'

    export default {
        name: 'AdminTablePagination',
        mixins: [PaginationMixin],
        props: {
          itemLabels: {
              type: Object,
              default: () => {
                  return {
                      singular: Craft.t('app', 'Item'),
                      plural: Craft.t('app', 'Items')
                  }
              }
          }
        },
        computed: {
            countWording() {
                return this.tablePagination.total == 1 ? this.itemLabels.singular : this.itemLabels.plural;
            }
        }
    }
</script>

<style land="scss">
  .vue-admin-table-pagination {
    border-top: 1px solid #f3f7fc;
    margin-top: 14px;
    padding-top: 14px;
  }
</style>