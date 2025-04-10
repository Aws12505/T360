We're building a Performance Summary Dashboard using Vue and the shadcn-vue component library. Your goal is to replicate the layout and functionality exactly as shown in the provided screenshots. Below is the breakdown:

✅ Folder Structure
Create a new folder named: summary

All components related to this performance dashboard (on-time, acceptance, safety) will live in this folder.

Final output will be one single page, combining all sections (just like the first main dashboard image).

📊 Chart Library
Use this donut chart from shadcn-vue:
https://www.shadcn-vue.com/docs/charts/donut.html

📁 Components to Build (inside summary/ folder)
1. Tabs Header Section
Tabs: On-Time, Acceptance, Safety

The active tab should be styled with a highlighted color (as shown in blue in the screenshot).

When clicked, it should change the component below (use conditional rendering).

2. On-Time Tab Content
Screenshot Ref: Second Image

Table: "Total delays by driver"

Columns: Driver Name, OTO, OTD

Each row repeats the same data for now (we'll switch it to dynamic later).

Include a See details .. link.

Donut Chart: "Delays By Reason"

Legend labels: Mechanical tractors, Driver arriving late, Medical, HOS

Show percentages like: 90%, 6%, 2%, etc.

Use colors similar to the screenshot (dark to light blue tones).

3. Acceptance Tab Content
Screenshot Ref: Third Image

Table: "Total rejections by driver"

Columns: Driver Name, Load rejections, Block rejections, Penalty

Driver: "Adam" is repeated across the rows for now.

Donut Chart: "Rejections By Reason"

Same structure and color logic as the on-time chart.

Legend: Mechanical tractors, Driver arriving late, Medical, HOS

4. Safety Tab Content
Screenshot Ref: Fourth Image

Left Panel: Top 5 Drivers

List with names and ranks (e.g., Daniel Rice - 1, Johnny Rice - 2...)

Middle Panel: Bottom 5 Drivers

Same structure as the Top 5, just different names and ranks.

Right Panel: Donut Chart – "Safety Summary"

Labels: Speeding Violations, Traffic Light Violation, Following Distance Hard Brake, Driver Distraction, Sign Violations

Each slice should match the color tone in the screenshot.

Percentages: e.g., Speeding Violations – 40.3%, Traffic Light – 31%, etc.

📌 Additional Notes
All data is currently static, prepare the structure to be switched easily to real API data later.

Keep components modular and reusable, especially the chart and table sections.

Use Tailwind or shadcn-vue components wherever possible for consistency.

Match the design exactly like the screenshot (font, padding, hover states).