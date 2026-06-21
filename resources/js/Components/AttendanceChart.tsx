/**
 * AttendanceChart
 * ---------------
 * Enterprise ECharts component — Pie, Column, Bar, Line.
 *
 * To change categories/colors, only edit the SERIES array below.
 * To wire up dynamic data, pass the `chart` prop from Laravel via Inertia.
 */

import MaterialIcon from "@/Components/MaterialIcon";
import type { AttendanceReportChart } from "@/types";
import ReactECharts from "echarts-for-react";
import { useState } from "react";

// ─── Chart type config ────────────────────────────────────────────────────────

type ChartType = "pie" | "column" | "bar" | "line";

const TABS = [
    { type: "pie" as const, label: "Pie", icon: "pie_chart" },
    { type: "column" as const, label: "Column", icon: "bar_chart" },
    { type: "bar" as const, label: "Bar", icon: "align_horizontal_left" },
    { type: "line" as const, label: "Line", icon: "show_chart" },
];

// ─── Data series — edit here to add/rename/recolor categories ────────────────

const SERIES = [
    { key: "wfo" as const, label: "WFO", color: "#4ade80", light: "#bbf7d0" },
    { key: "wfh" as const, label: "WFH", color: "#38bdf8", light: "#bae6fd" },
    {
        key: "dinas" as const,
        label: "Dinas",
        color: "#c084fc",
        light: "#e9d5ff",
    },
    { key: "izin" as const, label: "Izin", color: "#818cf8", light: "#c7d2fe" },
    {
        key: "tidak_hadir" as const,
        label: "Tidak Hadir",
        color: "#f87171",
        light: "#fecaca",
    },
] satisfies {
    key: keyof AttendanceReportChart;
    label: string;
    color: string;
    light: string;
}[];

// ─── Shared ECharts tokens ────────────────────────────────────────────────────

const tooltipBase = {
    backgroundColor: "#fff",
    borderColor: "#e5e7eb",
    borderWidth: 1,
    textStyle: { color: "#374151", fontSize: 12 },
    extraCssText:
        "box-shadow:0 8px 28px rgba(0,0,0,0.12);border-radius:12px;padding:10px 16px;",
};

const axisLine = { lineStyle: { color: "#e5e7eb" } };
const axisLabel = { color: "#9ca3af", fontSize: 12 };
const splitLine = { lineStyle: { color: "#f3f4f6", type: "dashed" as const } };

// ─── Helpers ──────────────────────────────────────────────────────────────────

function pctOf(value: number, total: number) {
    return total > 0 ? ((value / total) * 100).toFixed(1) : "0.0";
}

// ─── Component ────────────────────────────────────────────────────────────────

export interface AttendanceChartProps {
    chart: AttendanceReportChart;
}

export default function AttendanceChart({ chart }: AttendanceChartProps) {
    const [activeTab, setActiveTab] = useState<ChartType>("pie");

    const labels = SERIES.map((s) => s.label);
    const values = SERIES.map((s) => chart[s.key]);
    const total = values.reduce((a, b) => a + b, 0);

    // ── PIE ──────────────────────────────────────────────────────────────────

    function getPieOption() {
        return {
            tooltip: {
                ...tooltipBase,
                trigger: "item",
                formatter: (p: any) =>
                    `<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                       <span style="width:10px;height:10px;border-radius:50%;background:${p.color};display:inline-block"></span>
                       <b style="font-size:13px;color:#111827">${p.name}</b>
                     </div>
                     <div style="padding-left:18px;line-height:1.8">
                       <span style="font-size:22px;font-weight:800;color:${p.color}">${p.value}</span>
                       <span style="color:#9ca3af;margin-left:6px;font-size:12px">orang</span><br/>
                       <span style="color:#6b7280;font-size:12px">${p.percent}% dari total</span>
                     </div>`,
            },
            legend: { show: false },
            series: [
                {
                    type: "pie",
                    radius: "66%",
                    center: ["50%", "50%"],
                    selectedMode: "single",
                    selectedOffset: 14,
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 8,
                        borderColor: "#fff",
                        borderWidth: 3,
                    },
                    label: {
                        show: true,
                        backgroundColor: "rgba(255,255,255,0.95)",
                        borderRadius: 6,
                        padding: [6, 10],
                        formatter: (p: any) =>
                            `{name|${p.name.toUpperCase()}:} {val|${p.value}} {pct|(${p.percent}%)}`,
                        rich: {
                            name: {
                                color: "#374151",
                                fontSize: 12,
                                fontWeight: "700",
                            },
                            val: {
                                color: "#111827",
                                fontSize: 13,
                                fontWeight: "700",
                            },
                            pct: {
                                color: "#9ca3af",
                                fontSize: 11,
                            },
                        },
                    },
                    labelLine: {
                        show: true,
                        smooth: 0.3,
                        length: 12,
                        length2: 16,
                    },
                    emphasis: {
                        scale: true,
                        scaleSize: 8,
                        itemStyle: {
                            shadowBlur: 24,
                            shadowOffsetX: 0,
                            shadowColor: "rgba(0,0,0,0.18)",
                        },
                    },
                    data: SERIES.map((s) => ({
                        name: s.label,
                        value: chart[s.key],
                        itemStyle: { color: s.color },
                        labelLine: {
                            lineStyle: { color: s.color, width: 1.5 },
                        },
                    })),
                },
            ],
        };
    }

    // ── COLUMN ───────────────────────────────────────────────────────────────

    function getColumnOption() {
        return {
            tooltip: {
                ...tooltipBase,
                trigger: "axis",
                axisPointer: {
                    type: "shadow",
                    shadowStyle: { color: "rgba(0,0,0,0.03)" },
                },
                formatter: (params: any[]) => {
                    const p = params[0];
                    const s = SERIES[p.dataIndex];
                    const pc = pctOf(p.value, total);
                    return `<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                              <span style="width:10px;height:10px;border-radius:50%;background:${s.color};display:inline-block"></span>
                              <b style="font-size:13px;color:#111827">${p.name}</b>
                            </div>
                            <div style="padding-left:18px;line-height:1.8">
                              <span style="font-size:22px;font-weight:800;color:${s.color}">${p.value}</span>
                              <span style="color:#9ca3af;font-size:12px;margin-left:6px">orang</span><br/>
                              <span style="color:#6b7280;font-size:12px">${pc}% dari total</span>
                            </div>`;
                },
            },
            grid: {
                top: 30,
                right: 20,
                bottom: 44,
                left: 20,
                containLabel: true,
            },
            xAxis: {
                type: "category",
                data: labels,
                axisLine,
                axisTick: { show: false },
                axisLabel: { ...axisLabel, fontWeight: "600" },
                splitLine: { show: false },
            },
            yAxis: {
                type: "value",
                axisLine: { show: false },
                axisTick: { show: false },
                axisLabel,
                splitLine,
                min: 0,
            },
            series: [
                {
                    type: "bar",
                    barMaxWidth: 58,
                    showBackground: true,
                    backgroundStyle: {
                        color: "#f9fafb",
                        borderRadius: [10, 10, 0, 0],
                    },
                    data: SERIES.map((s) => ({
                        value: chart[s.key],
                        itemStyle: {
                            borderRadius: [10, 10, 0, 0],
                            color: s.color,
                        },
                    })),
                    label: {
                        show: true,
                        position: "top",
                        formatter: (p: any) => `{v|${p.value}}`,
                        rich: {
                            v: {
                                color: "#374151",
                                fontSize: 12,
                                fontWeight: "700",
                                lineHeight: 22,
                            },
                        },
                    },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 20,
                            shadowColor: "rgba(0,0,0,0.16)",
                        },
                    },
                },
            ],
        };
    }

    // ── BAR (horizontal) ─────────────────────────────────────────────────────

    function getBarOption() {
        const rev = [...SERIES].reverse();
        return {
            tooltip: {
                ...tooltipBase,
                trigger: "axis",
                axisPointer: {
                    type: "shadow",
                    shadowStyle: { color: "rgba(0,0,0,0.03)" },
                },
                formatter: (params: any[]) => {
                    const p = params[0];
                    const s = rev[p.dataIndex];
                    const pc = pctOf(p.value, total);
                    return `<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                              <span style="width:10px;height:10px;border-radius:50%;background:${s.color};display:inline-block"></span>
                              <b style="font-size:13px;color:#111827">${p.name}</b>
                            </div>
                            <div style="padding-left:18px;line-height:1.8">
                              <span style="font-size:22px;font-weight:800;color:${s.color}">${p.value}</span>
                              <span style="color:#9ca3af;font-size:12px;margin-left:6px">orang</span><br/>
                              <span style="color:#6b7280;font-size:12px">${pc}% dari total</span>
                            </div>`;
                },
            },
            grid: {
                top: 10,
                right: 70,
                bottom: 10,
                left: 10,
                containLabel: true,
            },
            xAxis: {
                type: "value",
                axisLine: { show: false },
                axisTick: { show: false },
                axisLabel,
                splitLine,
                min: 0,
            },
            yAxis: {
                type: "category",
                data: rev.map((s) => s.label),
                axisLine,
                axisTick: { show: false },
                axisLabel: { ...axisLabel, fontWeight: "700", fontSize: 13 },
                splitLine: { show: false },
            },
            series: [
                {
                    type: "bar",
                    barMaxWidth: 30,
                    showBackground: true,
                    backgroundStyle: {
                        color: "#f9fafb",
                        borderRadius: [0, 8, 8, 0],
                    },
                    data: rev.map((s) => ({
                        value: chart[s.key],
                        itemStyle: {
                            borderRadius: [0, 8, 8, 0],
                            color: s.color,
                        },
                    })),
                    label: {
                        show: true,
                        position: "right",
                        formatter: (p: any) => `{v|${p.value}}`,
                        rich: {
                            v: {
                                color: "#374151",
                                fontSize: 12,
                                fontWeight: "700",
                                padding: [0, 0, 0, 8],
                            },
                        },
                    },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 20,
                            shadowColor: "rgba(0,0,0,0.16)",
                        },
                    },
                },
            ],
        };
    }

    // ── LINE ──────────────────────────────────────────────────────────────────

    function getLineOption() {
        return {
            tooltip: {
                ...tooltipBase,
                trigger: "axis",
                formatter: (params: any[]) => {
                    const p = params[0];
                    const s = SERIES[p.dataIndex];
                    const pc = pctOf(p.value, total);
                    return `<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                              <span style="width:10px;height:10px;border-radius:50%;background:${s.color};display:inline-block"></span>
                              <b style="font-size:13px;color:#111827">${p.name}</b>
                            </div>
                            <div style="padding-left:18px;line-height:1.8">
                              <span style="font-size:22px;font-weight:800;color:${s.color}">${p.value}</span>
                              <span style="color:#9ca3af;font-size:12px;margin-left:6px">orang</span><br/>
                              <span style="color:#6b7280;font-size:12px">${pc}% dari total</span>
                            </div>`;
                },
            },
            grid: {
                top: 30,
                right: 20,
                bottom: 44,
                left: 20,
                containLabel: true,
            },
            xAxis: {
                type: "category",
                data: labels,
                axisLine,
                axisTick: { show: false },
                axisLabel: { ...axisLabel, fontWeight: "600" },
                splitLine: { show: false },
                boundaryGap: false,
            },
            yAxis: {
                type: "value",
                axisLine: { show: false },
                axisTick: { show: false },
                axisLabel,
                splitLine,
                min: 0,
            },
            series: [
                {
                    type: "line",
                    smooth: true,
                    symbol: "circle",
                    symbolSize: 12,
                    // Rainbow gradient line connecting all points
                    lineStyle: {
                        width: 3.5,
                        shadowColor: "rgba(0,0,0,0.14)",
                        shadowBlur: 10,
                        shadowOffsetY: 4,
                        color: {
                            type: "linear",
                            x: 0,
                            y: 0,
                            x2: 1,
                            y2: 0,
                            colorStops: SERIES.map((s, i) => ({
                                offset: i / (SERIES.length - 1),
                                color: s.color,
                            })),
                        },
                    },
                    // Each point gets its own category color
                    data: SERIES.map((s) => ({
                        value: chart[s.key],
                        itemStyle: {
                            color: s.color,
                            borderColor: "#fff",
                            borderWidth: 3,
                        },
                    })),
                    label: {
                        show: true,
                        position: "top",
                        formatter: (p: any) => `{v|${p.value}}`,
                        rich: {
                            v: {
                                color: "#374151",
                                fontSize: 11,
                                fontWeight: "700",
                                lineHeight: 22,
                            },
                        },
                    },
                    areaStyle: {
                        color: {
                            type: "linear",
                            x: 0,
                            y: 0,
                            x2: 0,
                            y2: 1,
                            colorStops: [
                                { offset: 0, color: "rgba(234,179,8,0.18)" },
                                { offset: 1, color: "rgba(234,179,8,0.00)" },
                            ],
                        },
                    },
                    emphasis: {
                        scale: true,
                        itemStyle: {
                            shadowBlur: 14,
                            shadowColor: "rgba(0,0,0,0.2)",
                        },
                    },
                },
            ],
        };
    }

    const optionMap: Record<ChartType, () => object> = {
        pie: getPieOption,
        column: getColumnOption,
        bar: getBarOption,
        line: getLineOption,
    };

    return (
        <div className="rounded-xl border border-outline-variant bg-white shadow-sm">
            {/* Header */}
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant px-6 py-4">
                <div className="flex items-center gap-2">
                    <MaterialIcon
                        name="bar_chart"
                        style={{ fontSize: 22, color: "#9ca3af" }}
                    />
                    <h3 className="text-base font-bold text-on-surface">
                        Grafik Distribusi Kehadiran
                    </h3>
                </div>

                {/* Chart type tabs */}
                <div className="flex gap-1 rounded-lg border border-outline-variant bg-gray-50 p-1">
                    {TABS.map((tab) => (
                        <button
                            key={tab.type}
                            type="button"
                            onClick={() => setActiveTab(tab.type)}
                            className={`inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all duration-200 ${
                                activeTab === tab.type
                                    ? "bg-[#eab308] text-white shadow-sm"
                                    : "text-gray-500 hover:text-gray-700"
                            }`}
                        >
                            <MaterialIcon
                                name={tab.icon}
                                style={{ fontSize: 14 }}
                            />
                            {tab.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Chart area */}
            <div className="p-4">
                <ReactECharts
                    key={activeTab}
                    option={optionMap[activeTab]()}
                    style={{ height: 400 }}
                    opts={{ renderer: "canvas" }}
                    notMerge
                />
            </div>
        </div>
    );
}
