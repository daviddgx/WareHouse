$(function () {

    // ==============================================================
    // Campaign
    // ==============================================================

    var chart1 = c3.generate({
        bindto: '#campaign-v2',
        data: {
            columns: [
                ['Direct Sales', 25],
                ['Referral Sales', 15],
                ['Afilliate Sales', 10],
                ['Indirect Sales', 15]
            ],

            type: 'donut',
            tooltip: {
                show: true
            }
        },
        donut: {
            label: {
                show: false
            },
            title: 'Sales',
            width: 18
        },

        legend: {
            hide: true
        },
        color: {
            pattern: [
                '#edf2f6',
                '#5f76e8',
                '#ff4f70',
                '#01caf1'
            ]
        },
        transition: {
            duration: 600
        }
    });

    d3.select('#campaign-v2 .c3-chart-arcs-title').style('font-family', 'Rubik');

    // ============================================================== 
    // income
    // ============================================================== 
    var data = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        series: [
            [5, 4, 3, 7, 5, 10]
        ]
    };

    var options = {
        axisX: {
            showGrid: false
        },
        seriesBarDistance: 1,
        chartPadding: {
            top: 15,
            right: 15,
            bottom: 5,
            left: 0
        },
        plugins: [
            Chartist.plugins.tooltip()
        ],
        width: '100%'
    };

    var responsiveOptions = [
        ['screen and (max-width: 640px)', {
            seriesBarDistance: 5,
            axisX: {
                labelInterpolationFnc: function (value) {
                    return value[0];
                }
            }
        }]
    ];
    var incomeChart = new Chartist.Bar('.net-income', data, options, responsiveOptions);

    incomeChart.on('draw', function (ctx) {
        if (ctx.type === 'bar' && ctx.element.animate) {
            var delay = ctx.index * 120;

            ctx.element.animate({
                y2: {
                    begin: delay,
                    dur: 600,
                    from: ctx.y1,
                    to: ctx.y2,
                    easing: Chartist.Svg.Easing.easeOutQuart
                },
                opacity: {
                    begin: delay,
                    dur: 600,
                    from: 0,
                    to: 1,
                    easing: Chartist.Svg.Easing.easeOutQuart
                }
            });
        } else if (ctx.type === 'grid' && ctx.element.animate) {
            var animations = {};
            animations[ctx.axis.units.pos + '1'] = {
                begin: 0,
                dur: 400,
                from: ctx[ctx.axis.units.pos + '1'] - 30,
                to: ctx[ctx.axis.units.pos + '1'],
                easing: Chartist.Svg.Easing.easeOutQuart
            };
            animations[ctx.axis.units.pos + '2'] = {
                begin: 0,
                dur: 400,
                from: ctx[ctx.axis.units.pos + '2'] - 30,
                to: ctx[ctx.axis.units.pos + '2'],
                easing: Chartist.Svg.Easing.easeOutQuart
            };
            animations.opacity = {
                begin: 0,
                dur: 400,
                from: 0,
                to: 1
            };

            ctx.element.animate(animations);
        }
    });

    incomeChart.on('created', function () {
        if (incomeChart.container) {
            incomeChart.container.__chartistInstance = incomeChart;
        }
    });

    // ============================================================== 
    // Visit By Location
    // ==============================================================
    jQuery('#visitbylocate').vectorMap({
        map: 'world_mill_en',
        backgroundColor: 'transparent',
        borderColor: '#000',
        borderOpacity: 0,
        borderWidth: 0,
        zoomOnScroll: false,
        color: '#d5dce5',
        regionStyle: {
            initial: {
                fill: '#d5dce5',
                'stroke-width': 1,
                'stroke': 'rgba(255, 255, 255, 0.5)'
            }
        },
        enableZoom: true,
        hoverColor: '#bdc9d7',
        hoverOpacity: null,
        normalizeFunction: 'linear',
        scaleColors: ['#d5dce5', '#d5dce5'],
        selectedColor: '#bdc9d7',
        selectedRegions: [],
        showTooltip: true,
        onRegionClick: function (element, code, region) {
            var message = 'You clicked "' + region + '" which has the code: ' + code.toUpperCase();
            alert(message);
        }
    });

    // ==============================================================
    // Earning Stastics Chart
    // ==============================================================
    var statsChart = new Chartist.Line('.stats', {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        series: [
            [11, 10, 15, 21, 14, 23, 12]
        ]
    }, {
        low: 0,
        high: 28,
        showArea: true,
        fullWidth: true,
        plugins: [
            Chartist.plugins.tooltip()
        ],
        axisY: {
            onlyInteger: true,
            scaleMinSpace: 40,
            offset: 20,
            labelInterpolationFnc: function (value) {
                return (value / 1) + 'k';
            }
        },
    });

    var sequence = 0;
    var sequenceDelay = 80;
    var sequenceDuration = 500;

    statsChart.on('created', function (ctx) {
        sequence = 0;

        if (statsChart.container) {
            statsChart.container.__chartistInstance = statsChart;
        }

        var defs = ctx.svg.elem('defs');
        defs.elem('linearGradient', {
            id: 'gradient',
            x1: 0,
            y1: 1,
            x2: 0,
            y2: 0
        }).elem('stop', {
            offset: 0,
            'stop-color': 'rgba(255, 255, 255, 1)'
        }).parent().elem('stop', {
            offset: 1,
            'stop-color': 'rgba(80, 153, 255, 1)'
        });
    });

    // Offset x1 a tiny amount so that the straight stroke gets a bounding box and animate entries
    statsChart.on('draw', function (ctx) {
        if (ctx.type === 'area') {
            ctx.element.attr({
                x1: ctx.x1 + 0.001
            });
        }

        if (!ctx.element.animate) {
            return;
        }

        sequence += 1;

        if (ctx.type === 'line' || ctx.type === 'area') {
            ctx.element.animate({
                d: {
                    begin: sequence * sequenceDelay,
                    dur: sequenceDuration + 200,
                    from: ctx.path.clone().scale(1, 0).translate(0, ctx.chartRect.height()).stringify(),
                    to: ctx.path.clone().stringify(),
                    easing: Chartist.Svg.Easing.easeOutQuint
                }
            });
        } else if (ctx.type === 'point') {
            ctx.element.animate({
                x1: {
                    begin: sequence * sequenceDelay,
                    dur: sequenceDuration,
                    from: ctx.x - 10,
                    to: ctx.x,
                    easing: Chartist.Svg.Easing.easeOutQuart
                },
                x2: {
                    begin: sequence * sequenceDelay,
                    dur: sequenceDuration,
                    from: ctx.x - 10,
                    to: ctx.x,
                    easing: Chartist.Svg.Easing.easeOutQuart
                },
                opacity: {
                    begin: sequence * sequenceDelay,
                    dur: sequenceDuration,
                    from: 0,
                    to: 1,
                    easing: Chartist.Svg.Easing.easeOutQuart
                }
            });
        } else if (ctx.type === 'grid') {
            var animations = {};
            animations[ctx.axis.units.pos + '1'] = {
                begin: sequence * sequenceDelay,
                dur: sequenceDuration,
                from: ctx[ctx.axis.units.pos + '1'] - 30,
                to: ctx[ctx.axis.units.pos + '1'],
                easing: Chartist.Svg.Easing.easeOutQuart
            };
            animations[ctx.axis.units.pos + '2'] = {
                begin: sequence * sequenceDelay,
                dur: sequenceDuration,
                from: ctx[ctx.axis.units.pos + '2'] - 30,
                to: ctx[ctx.axis.units.pos + '2'],
                easing: Chartist.Svg.Easing.easeOutQuart
            };
            animations.opacity = {
                begin: sequence * sequenceDelay,
                dur: sequenceDuration,
                from: 0,
                to: 1,
                easing: Chartist.Svg.Easing.easeOutQuart
            };

            ctx.element.animate(animations);
        }
    });

    $(window).on('resize', function () {
        statsChart.update();
        incomeChart.update();
    });
})