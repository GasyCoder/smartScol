{{-- Vue dashboard principale --}}
<div>
@include('livewire.pages.header-dashboard')
<div class="grid grid-cols-12 grid-flow-dense gap-7">
    @include('livewire.pages.counter-principale')
    @include('livewire.pages.graphiques')
    @include('livewire.pages.store-statistique')
    @include('livewire.pages.table-statistique')
    @include('livewire.pages.top-etudiants')
</div>
<!-- grid -->
</div>

@push('scripts')
    @vite(['resources/dashwin/js/charts.js'])
    <script type="module">

        // Line Charts
        var todayOrders = {
            labels : ["12AM - 02AM", "02AM - 04AM", "04AM - 06AM", "06AM - 08AM", "08AM - 10AM", "10AM - 12PM", "12PM - 02PM", "02PM - 04PM", "04PM - 06PM", "06PM - 08PM", "08PM - 10PM", "10PM - 12PM"],
            dataUnit : 'Orders',
            lineTension : .3,
            datasets : [{
                label : "Orders",
                color : "#854fff",
                background : "transparent",
                data: [92, 105, 125, 85, 110, 106, 131, 105, 110, 131, 105, 110]
            }]
        };
        Line({selector:'#todayOrders', data:todayOrders, tooltip: "tooltipSmallDark", scales: "noScale" });

        var todayRevenue = {
            labels : ["12AM - 02AM", "02AM - 04AM", "04AM - 06AM", "06AM - 08AM", "08AM - 10AM", "10AM - 12PM", "12PM - 02PM", "02PM - 04PM", "04PM - 06PM", "06PM - 08PM", "08PM - 10PM", "10PM - 12PM"],
            dataUnit : 'Orders',
            lineTension : .3,
            datasets : [{
                label : "Revenue",
                color : "#33d895",
                background : "transparent",
                data: [92, 105, 125, 85, 110, 106, 131, 105, 110, 131, 105, 110]
            }]
        };
        Line({selector:'#todayRevenue', data:todayRevenue, tooltip: "tooltipSmallDark", scales: "noScale" });

        var todayCustomers = {
            labels : ["12AM - 02AM", "02AM - 04AM", "04AM - 06AM", "06AM - 08AM", "08AM - 10AM", "10AM - 12PM", "12PM - 02PM", "02PM - 04PM", "04PM - 06PM", "06PM - 08PM", "08PM - 10PM", "10PM - 12PM"],
            dataUnit : 'Orders',
            lineTension : .3,
            datasets : [{
                label : "Customers",
                color : "#ff63a5",
                background : "transparent",
                data: [92, 105, 125, 85, 110, 106, 131, 105, 110, 131, 105, 110]
            }]
        };
        Line({selector:'#todayCustomers', data:todayCustomers, tooltip: "tooltipSmallDark", scales: "noScale" });

        var todayVisitors = {
            labels : ["12AM - 02AM", "02AM - 04AM", "04AM - 06AM", "06AM - 08AM", "08AM - 10AM", "10AM - 12PM", "12PM - 02PM", "02PM - 04PM", "04PM - 06PM", "06PM - 08PM", "08PM - 10PM", "10PM - 12PM"],
            dataUnit : 'Orders',
            lineTension : .3,
            datasets : [{
                label : "Visitors",
                color : "#559bfb",
                background : "transparent",
                data: [92, 105, 125, 85, 110, 106, 131, 105, 110, 131, 105, 110]
            }]
        };
        Line({selector:'#todayVisitors', data:todayVisitors, tooltip: "tooltipSmallDark", scales: "noScale" });

        var salesStatistics = {
            labels : ["01 Jan", "02 Jan", "03 Jan", "04 Jan", "05 Jan", "06 Jan", "07 Jan", "08 Jan", "09 Jan", "10 Jan", "11 Jan", "12 Jan","13 Jan", "14 Jan", "15 Jan", "16 Jan", "17 Jan", "18 Jan", "19 Jan", "20 Jan", "21 Jan", "22 Jan", "23 Jan", "24 Jan", "25 Jan", "26 Jan", "27 Jan", "28 Jan", "29 Jan", "30 Jan"],
            dataUnit : 'People',
            lineTension : .4,
            datasets : [{
                label : "Total orders",
                color : "#9d72ff",
                dash : [0,0],
                background : hexRGB('#9d72ff',.15),
                data: [3710, 4820, 4810, 5480, 5300, 5670, 6660, 4830, 5590, 5730, 4790, 4950, 5100, 5800, 5950, 5850, 5950, 4450, 4900, 8000, 7200, 7250, 7900, 8950,6300, 7200, 7250, 7650, 6950, 4750]
            },{
                label : "Canceled orders",
                color : "#eb6459",
                dash : [5,5],
                background : "transparent",
                data: [110, 220, 810, 480, 600, 670, 660, 830, 590, 730, 790, 950, 100, 800, 950, 850, 950, 450, 900, 0, 200, 250, 900, 950, 300, 200, 250, 650, 950, 750]
            }]
        };
        Line({selector:'#salesStatistics', data:salesStatistics, tooltip: "tooltipDark", scales: "scales2" });

        // Doughnut Charts
        var orderStatistics = {
            labels : ["Completed", "Processing", "Canclled"],
            dataUnit : 'People',
            legend: false,
            datasets : [{
                borderColor : "#fff",
                background : ["#816bff","#13c9f2","#ff82b7"],
                data: [4305, 859, 482]
            }]
        };
        Doughnut({selector:'#orderStatistics', data:orderStatistics, tooltip: "tooltipDark"});

    </script>
@endpush