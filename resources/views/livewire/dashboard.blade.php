<div>
<div class="relative flex items-center justify-between pb-5 md:pb-7">
    <div>
        <h3 class="mb-2 text-2xl font-bold tracking-tight font-heading lg:text-3xl leading-tighter text-slate-700 dark:text-white">Ecommerce Dashboard</h3>
        <p class="text-slate-400">Welcome to DashWind Dashboard Template.</p>
    </div>
    <div>
        <button data-target="#pageOptions" class="class-toggle sm:hidden *:pointer-events-none -me-2 inline-flex items-center justify-center isolate relative h-9 w-9 px-1.5 before:content-[''] before:absolute before:-z-[1] before:h-5 before:w-5 hover:before:h-10 hover:before:w-10 [&.active]:before:h-10 [&.active]:before:w-10 before:rounded-full before:opacity-0 hover:before:opacity-100 [&.active]:before:opacity-100 before:transition-all before:duration-300 before:-translate-x-1/2  before:-translate-y-1/2 before:top-1/2 before:left-1/2 before:bg-gray-200 dark:before:bg-gray-900">
            <em class="text-xl text-slate-600 dark:text-slate-300 ni ni-more-v"></em>
        </button>
        <div id="pageOptions" class="absolute sm:relative start-0 end-0 top-full -mx-3.5 bg-white dark:bg-gray-950 sm:bg-transparent sm:dark:bg-transparent max-sm:shadow max-sm:dark:shadow-gray-800 opacity-0 invisible sm:opacity-100 sm:visible [&.active]:opacity-100 [&.active]:visible z-[1000]">
            <ul class="flex items-center gap-4 px-3.5 py-5 sm:py-0">
                <li>
                    <div class="relative dropdown">
                        <a href="#" data-offset="0,4" data-placement="bottom-end"  data-rtl-placement="bottom-start" class="dropdown-toggle *:pointer-events-none peer relative inline-flex items-center text-center align-middle text-sm font-bold leading-4.5 rounded px-5 py-2 tracking-wide border border-gray-300 dark:border-gray-900 text-slate-600 dark:text-slate-200 bg-white dark:bg-gray-900 hover:bg-slate-600 [&.show]:bg-slate-600 hover:dark:bg-gray-800 [&.show]:dark:bg-gray-800 hover:text-white [&.show]:text-white hover:dark:text-white [&.show]:dark:text-white hover:border-slate-600 hover:dark:border-gray-800 [&.show]:dark:border-gray-800 active:bg-slate-700 active:text-white active:border-slate-600 transition-all duration-300">
                            <em class="text-xl leading-4.5 me-3 hidden sm:inline ni ni-calender-date"></em>
                            <span class="me-4"><span class="hidden md:inline">Last</span> 30 Days</span>
                            <em class="text-xl leading-4.5 rtl:-scale-x-100 ni ni-chevron-right"></em>
                        </a>
                        <div class="dropdown-menu absolute min-w-[180px] border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 rounded-md shadow hidden peer-[.show]:block z-[1000]">
                            <ul class="py-2">
                                <li><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Last 30 Days</span></a></li>
                                <li><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Last 6 Months</span></a></li>
                                <li><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Last 1 Years</span></a></li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li class="ms-auto">
                    <a href="#" class="relative inline-flex items-center text-center align-middle text-sm font-bold leading-4.5 rounded px-5 py-2 tracking-wide border border-primary-600 text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 transition-all duration-300">
                        <em class="text-xl leading-4.5 ni ni-reports"></em><span class="ms-3">Reports</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div><!-- block head -->
<div class="grid grid-cols-12 grid-flow-dense gap-7">
    <div class="col-span-12 sm:col-span-6 2xl:col-span-3">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 sm:p-6">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Today Orders</h6>
                <div class="flex items-center justify-between my-2">
                    <div class="text-3xl font-medium text-slate-700 dark:text-white">1,945</div>
                    <div class="w-24 h-10">
                        <canvas class="ecommerce-line-chart-small" id="todayOrders"></canvas>
                    </div>
                </div>
                <div class="text-sm"><span class="text-green-600"><em class="icon ni ni-arrow-long-up"></em>4.63%</span><span> vs. last week</span></div>
            </div>
        </div>
    </div><!-- col -->
    <div class="col-span-12 sm:col-span-6 2xl:col-span-3">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 sm:p-6">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Today Revenue</h6>
                <div class="flex items-center justify-between my-2">
                    <div class="text-3xl font-medium text-slate-700 dark:text-white">$2,338</div>
                    <div class="w-24 h-10">
                        <canvas class="ecommerce-line-chart-small" id="todayRevenue"></canvas>
                    </div>
                </div>
                <div class="text-sm"><span class="text-red-600"><em class="icon ni ni-arrow-long-down"></em>2.34%</span><span> vs. last week</span></div>
            </div>
        </div>
    </div><!-- col -->
    <div class="col-span-12 sm:col-span-6 2xl:col-span-3">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 sm:p-6">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Today Customers</h6>
                <div class="flex items-center justify-between my-2">
                    <div class="text-3xl font-medium text-slate-700 dark:text-white">847</div>
                    <div class="w-24 h-10">
                        <canvas class="ecommerce-line-chart-small" id="todayCustomers"></canvas>
                    </div>
                </div>
                <div class="text-sm"><span class="text-green-600"><em class="icon ni ni-arrow-long-up"></em>4.63%</span><span> vs. last week</span></div>
            </div>
        </div>
    </div><!-- col -->
    <div class="col-span-12 sm:col-span-6 2xl:col-span-3">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 sm:p-6">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Today Visitors</h6>
                <div class="flex items-center justify-between my-2">
                    <div class="text-3xl font-medium text-slate-700 dark:text-white">23,485</div>
                    <div class="w-24 h-10">
                        <canvas class="ecommerce-line-chart-small" id="todayVisitors"></canvas>
                    </div>
                </div>
                <div class="text-sm"><span class="text-red-600"><em class="icon ni ni-arrow-long-down"></em>2.34%</span><span> vs. last week</span></div>
            </div>
        </div>
    </div><!-- col -->
    <div class="col-span-12 2xl:col-span-6">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 sm:p-6">
                <div class="flex items-center justify-between mb-3 gap-x-3">
                    <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Today Orders</h6>
                    <div class="relative dropdown">
                        <a href="#" data-offset="0,4" data-placement="bottom-end"  data-rtl-placement="bottom-start" class="dropdown-toggle *:pointer-events-none peer relative inline-flex items-center text-xs font-bold tracking-wide text-slate-400 hover:text-slate-600 [&.show]:text-slate-600 transition-all duration-300">
                            <span>Weekly</span>
                            <em class="text-base leading-4.5 ni ni-chevron-down"></em>
                        </a>
                        <div class="dropdown-menu absolute min-w-[180px] border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 rounded-md shadow hidden peer-[.show]:block z-[1000]">
                            <ul class="py-2">
                                <li class="group"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Daily</span><em class="hidden group-[.active]:block text-xs font-medium leading-none absolute top-1/2 end-4 -translate-y-1/2 ni ni-check-thick"></em></a></li>
                                <li class="group active"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Weekly</span><em class="hidden group-[.active]:block text-xs font-medium leading-none absolute top-1/2 end-4 -translate-y-1/2 ni ni-check-thick"></em></a></li>
                                <li class="group"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Monthly</span><em class="hidden group-[.active]:block text-xs font-medium leading-none absolute top-1/2 end-4 -translate-y-1/2 ni ni-check-thick"></em></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <ul class="flex flex-wrap justify-center mb-3 gap-x-8 gap-y-2">
                    <li>
                        <div class="flex items-center text-sm text-slate-400">
                            <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#6576ff]"></span>
                            <span>Total Order</span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center text-sm text-slate-400">
                            <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#eb6459]"></span>
                            <span>Cancelled Order</span>
                        </div>
                    </li>
                </ul>
                <div class="h-52">
                    <canvas class="ecommerce-line-chart" id="salesStatistics"></canvas>
                </div>
                <div class="flex justify-between mt-2 ms-11">
                    <div class="text-xs text-slate-400">01 Jan, 2020</div>
                    <div class="text-xs text-slate-400">30 Jan, 2020</div>
                </div>
            </div>
        </div>
    </div><!-- col -->
    <div class="col-span-12 md:col-span-6 2xl:col-span-3">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 sm:p-6">
                <h6 class="mb-6 text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Order Statistics</h6>
                <div class="h-44">
                    <canvas class="ecommerce-doughnut-chart" id="orderStatistics"></canvas>
                </div>
                <ul class="flex flex-wrap justify-center mt-5 gap-x-8 gap-y-2">
                    <li>
                        <div class="flex items-center text-sm text-slate-400">
                            <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#816bff]"></span>
                            <span>Completed</span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center text-sm text-slate-400">
                            <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#13c9f2]"></span>
                            <span>Processing</span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center text-sm text-slate-400">
                            <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#ff82b7]"></span>
                            <span>Cancelled</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div><!-- col -->
    <div class="col-span-12 md:col-span-6 2xl:col-span-3">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 sm:p-6">
                <h6 class="mb-3 text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Store Statistics</h6>
                <ul class="flex flex-col py-2 gap-y-4">
                    <li class="flex items-center justify-between">
                        <div>
                            <div class="mb-1 text-sm text-slate-400">Orders</div>
                            <div class="text-xl font-bold text-slate-700 dark:text-white">1,795</div>
                        </div>
                        <em class="flex items-center justify-center rounded-md text-2xl/none h-11 w-11 text-primary-600 bg-primary-100 dark:bg-primary-950 ni ni-bag"></em>
                    </li>
                    <li class="flex items-center justify-between">
                        <div>
                            <div class="mb-1 text-sm text-slate-400">Customers</div>
                            <div class="text-xl font-bold text-slate-700 dark:text-white">2,327</div>
                        </div>
                        <em class="flex items-center justify-center rounded-md text-2xl/none h-11 w-11 text-cyan-600 bg-cyan-100 dark:bg-cyan-950 ni ni-users"></em>
                    </li>
                    <li class="flex items-center justify-between">
                        <div>
                            <div class="mb-1 text-sm text-slate-400">Products</div>
                            <div class="text-xl font-bold text-slate-700 dark:text-white">674</div>
                        </div>
                        <em class="flex items-center justify-center text-pink-600 bg-pink-100 rounded-md text-2xl/none h-11 w-11 dark:bg-pink-950 ni ni-box"></em>
                    </li>
                    <li class="flex items-center justify-between">
                        <div>
                            <div class="mb-1 text-sm text-slate-400">Categories</div>
                            <div class="text-xl font-bold text-slate-700 dark:text-white">68</div>
                        </div>
                        <em class="flex items-center justify-center text-purple-600 bg-purple-100 rounded-md text-2xl/none h-11 w-11 dark:bg-purple-950 ni ni-server"></em>
                    </li>
                </ul>
            </div>
        </div>
    </div><!-- col -->
    <div class="col-span-12 2xl:col-span-8">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 pb-2 sm:p-6 sm:pb-2">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Recent Orders</h6>
            </div>
            <div class="table w-full text-sm text-slate-400">
                <div class="table-row [&>*]:border-b [&>*]:last:border-b-0">
                    <div class="relative table-cell py-2 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900"><span>Order No.</span></div>
                    <div class="relative hidden py-2 align-middle border-gray-300 sm:table-cell first:ps-6 last:pe-6 dark:border-gray-900"><span>Customer</span></div>
                    <div class="relative hidden py-2 align-middle border-gray-300 md:table-cell first:ps-6 last:pe-6 dark:border-gray-900"><span>Date</span></div>
                    <div class="relative table-cell py-2 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900"><span>Amount</span></div>
                    <div class="relative table-cell py-2 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900"><span class="hidden xs:inline">Status</span></div>
                </div>
                <div class="table-row transition-all duration-300 [&>*]:border-b [&>*]:last:border-b-0 hover:bg-gray-50 hover:dark:bg-gray-1000">
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span>
                            <a class="inline-flex text-sm font-medium leading-snug transition-all duration-300 whitespace-nowrap font-body text-primary-500 hover:text-primary-600" href="">#95954</a>
                        </span>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 sm:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <div class="flex items-center">
                            <div class="relative flex items-center justify-center flex-shrink-0 w-8 h-8 text-xs font-medium text-purple-500 bg-purple-100 rounded-full dark:bg-purple-950">
                                <span>AB</span>
                            </div>
                            <div class="flex items-center text-xs font-bold text-slate-600 dark:text-white ms-3">Abu Bin Ishityak</div>
                        </div>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 md:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs">02/11/2020</span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs font-medium text-slate-600 dark:text-white">4,596.75 <span class="font-normal text-slate-500">USD</span></span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="relative inline-flex ps-3 text-xs font-medium leading-4.5 align-middle tracking-snug whitespace-nowrap text-green-600 max-xs:w-0 max-xs:overflow-hidden before:absolute before:start-0  before:top-1/2  before:-translate-y-1/2  before:rounded-full  before:h-1.5  before:w-1.5  before:bg-current">Paid</span>
                    </div>
                </div><!-- row -->
                <div class="table-row transition-all duration-300 [&>*]:border-b [&>*]:last:border-b-0 hover:bg-gray-50 hover:dark:bg-gray-1000">
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span>
                            <a class="inline-flex text-sm font-medium leading-snug transition-all duration-300 whitespace-nowrap font-body text-primary-500 hover:text-primary-600" href="">#95850</a>
                        </span>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 sm:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <div class="flex items-center">
                            <div class="relative flex items-center justify-center flex-shrink-0 w-8 h-8 text-xs font-medium text-blue-600 bg-blue-100 rounded-full dark:bg-blue-950">
                                <span>DE</span>
                            </div>
                            <div class="flex items-center text-xs font-bold text-slate-600 dark:text-white ms-3">Desiree Edwards</div>
                        </div>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 md:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs">02/02/2020</span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs font-medium text-slate-600 dark:text-white">596.75 <span class="font-normal text-slate-500">USD</span></span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="relative inline-flex ps-3 text-xs font-medium leading-4.5 align-middle tracking-snug whitespace-nowrap text-red-600 max-xs:w-0 max-xs:overflow-hidden before:absolute before:start-0  before:top-1/2  before:-translate-y-1/2  before:rounded-full  before:h-1.5  before:w-1.5  before:bg-current">Canceled</span>
                    </div>
                </div><!-- row -->
                <div class="table-row transition-all duration-300 [&>*]:border-b [&>*]:last:border-b-0 hover:bg-gray-50 hover:dark:bg-gray-1000">
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span>
                            <a class="inline-flex text-sm font-medium leading-snug transition-all duration-300 whitespace-nowrap font-body text-primary-500 hover:text-primary-600" href="">#95812</a>
                        </span>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 sm:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <div class="flex items-center">
                            <div class="relative flex items-center justify-center flex-shrink-0 w-8 h-8 overflow-hidden rounded-full">
                                <img src="{{ asset('images/avatar/b-sm.jpg') }}" alt="">
                            </div>
                            <div class="flex items-center text-xs font-bold text-slate-600 dark:text-white ms-3">Blanca Schultz</div>
                        </div>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 md:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs">02/01/2020</span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs font-medium text-slate-600 dark:text-white">199.99 <span class="font-normal text-slate-500">USD</span></span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="relative inline-flex ps-3 text-xs font-medium leading-4.5 align-middle tracking-snug whitespace-nowrap text-green-600 max-xs:w-0 max-xs:overflow-hidden before:absolute before:start-0  before:top-1/2  before:-translate-y-1/2  before:rounded-full  before:h-1.5  before:w-1.5  before:bg-current">Paid</span>
                    </div>
                </div><!-- row -->
                <div class="table-row transition-all duration-300 [&>*]:border-b [&>*]:last:border-b-0 hover:bg-gray-50 hover:dark:bg-gray-1000">
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span>
                            <a class="inline-flex text-sm font-medium leading-snug transition-all duration-300 whitespace-nowrap font-body text-primary-500 hover:text-primary-600" href="">#95256</a>
                        </span>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 sm:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <div class="flex items-center">
                            <div class="relative flex items-center justify-center flex-shrink-0 w-8 h-8 text-xs font-medium text-indigo-600 bg-indigo-100 rounded-full dark:bg-indigo-950">
                                <span>NL</span>
                            </div>
                            <div class="flex items-center text-xs font-bold text-slate-600 dark:text-white ms-3">Naomi Lawrence</div>
                        </div>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 md:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs">01/29/2020</span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs font-medium text-slate-600 dark:text-white">1099.99 <span class="font-normal text-slate-500">USD</span></span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="relative inline-flex ps-3 text-xs font-medium leading-4.5 align-middle tracking-snug whitespace-nowrap text-green-600 max-xs:w-0 max-xs:overflow-hidden before:absolute before:start-0  before:top-1/2  before:-translate-y-1/2  before:rounded-full  before:h-1.5  before:w-1.5  before:bg-current">Paid</span>
                    </div>
                </div><!-- row -->
                <div class="table-row transition-all duration-300 [&>*]:border-b [&>*]:last:border-b-0 hover:bg-gray-50 hover:dark:bg-gray-1000">
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span>
                            <a class="inline-flex text-sm font-medium leading-snug transition-all duration-300 whitespace-nowrap font-body text-primary-500 hover:text-primary-600" href="">#95135</a>
                        </span>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 sm:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <div class="flex items-center">
                            <div class="relative flex items-center justify-center flex-shrink-0 w-8 h-8 text-xs font-medium text-green-600 bg-green-100 rounded-full dark:bg-green-950">
                                <span>CH</span>
                            </div>
                            <div class="flex items-center text-xs font-bold text-slate-600 dark:text-white ms-3">Cassandra Hogan</div>
                        </div>
                    </div>
                    <div class="relative hidden py-4 align-middle border-gray-300 md:table-cell first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs">01/29/2020</span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="text-xs font-medium text-slate-600 dark:text-white">1099.99 <span class="font-normal text-slate-500">USD</span></span>
                    </div>
                    <div class="relative table-cell py-4 align-middle border-gray-300 first:ps-6 last:pe-6 dark:border-gray-900">
                        <span class="relative inline-flex ps-3 text-xs font-medium leading-4.5 align-middle tracking-snug whitespace-nowrap text-yellow-600 max-xs:w-0 max-xs:overflow-hidden before:absolute before:start-0  before:top-1/2  before:-translate-y-1/2  before:rounded-full  before:h-1.5  before:w-1.5  before:bg-current">Due</span>
                    </div>
                </div><!-- row -->
            </div>
        </div>
    </div><!-- col -->
    <div class="col-span-12 md:col-span-8 lg:col-span-6 2xl:col-span-4">
        <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
            <div class="p-5 sm:p-6">
                <div class="flex items-center justify-between mb-3 gap-x-3">
                    <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Top products</h6>
                    <div class="relative dropdown">
                        <a href="#" data-offset="0,4" data-placement="bottom-end"  data-rtl-placement="bottom-start" class="dropdown-toggle *:pointer-events-none peer relative inline-flex items-center text-xs font-bold tracking-wide text-slate-400 hover:text-slate-600 [&.show]:text-slate-600 transition-all duration-300">
                            <span>Weekly</span>
                            <em class="text-base leading-4.5 ni ni-chevron-down"></em>
                        </a>
                        <div class="dropdown-menu absolute min-w-[180px] border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 rounded-md shadow hidden peer-[.show]:block z-[1000]">
                            <ul class="py-2">
                                <li class="group"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Daily</span><em class="hidden group-[.active]:block text-xs font-medium leading-none absolute top-1/2 end-4 -translate-y-1/2 ni ni-check-thick"></em></a></li>
                                <li class="group active"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Weekly</span><em class="hidden group-[.active]:block text-xs font-medium leading-none absolute top-1/2 end-4 -translate-y-1/2 ni ni-check-thick"></em></a></li>
                                <li class="group"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Monthly</span><em class="hidden group-[.active]:block text-xs font-medium leading-none absolute top-1/2 end-4 -translate-y-1/2 ni ni-check-thick"></em></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <ul class="flex flex-col gap-y-5 py-2.5 leading-tight">
                    <li class="flex items-center">
                        <div class="w-11">
                            <img class="rounded" src="{{ asset('images/product/a.png') }}" alt="">
                        </div>
                        <div class="ms-4">
                            <div class="text-base text-slate-700 dark:text-white">Pink Fitness Tracker</div>
                            <div class="text-sm text-slate-400">$99.00</div>
                        </div>
                        <div class="text-end ms-auto">
                            <div class="text-base text-slate-700 dark:text-white">$990.00</div>
                            <div class="text-sm text-slate-400">10 Sold</div>
                        </div>
                    </li>
                    <li class="flex items-center">
                        <div class="w-11">
                            <img class="rounded" src="{{ asset('images/product/b.png') }}" alt="">
                        </div>
                        <div class="ms-4">
                            <div class="text-base text-slate-700 dark:text-white">Purple Smartwatch</div>
                            <div class="text-sm text-slate-400">$99.00</div>
                        </div>
                        <div class="text-end ms-auto">
                            <div class="text-base text-slate-700 dark:text-white">$990.00</div>
                            <div class="text-sm text-slate-400">10 Sold</div>
                        </div>
                    </li>
                    <li class="flex items-center">
                        <div class="w-11">
                            <img class="rounded" src="{{ asset('images/product/c.png') }}" alt="">
                        </div>
                        <div class="ms-4">
                            <div class="text-base text-slate-700 dark:text-white">Black Smartwatch</div>
                            <div class="text-sm text-slate-400">$99.00</div>
                        </div>
                        <div class="text-end ms-auto">
                            <div class="text-base text-slate-700 dark:text-white">$990.00</div>
                            <div class="text-sm text-slate-400">10 Sold</div>
                        </div>
                    </li>
                    <li class="flex items-center">
                        <div class="w-11">
                            <img class="rounded" src="{{ asset('images/product/d.png') }}" alt="">
                        </div>
                        <div class="ms-4">
                            <div class="text-base text-slate-700 dark:text-white">Black Headphones</div>
                            <div class="text-sm text-slate-400">$99.00</div>
                        </div>
                        <div class="text-end ms-auto">
                            <div class="text-base text-slate-700 dark:text-white">$990.00</div>
                            <div class="text-sm text-slate-400">10 Sold</div>
                        </div>
                    </li>
                    <li class="flex items-center">
                        <div class="w-11">
                            <img class="rounded" src="{{ asset('images/product/e.png') }}" alt="">
                        </div>
                        <div class="ms-4">
                            <div class="text-base text-slate-700 dark:text-white">iPhone 7 Headphones</div>
                            <div class="text-sm text-slate-400">$99.00</div>
                        </div>
                        <div class="text-end ms-auto">
                            <div class="text-base text-slate-700 dark:text-white">$990.00</div>
                            <div class="text-sm text-slate-400">10 Sold</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div><!-- col -->
</div><!-- grid -->
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
