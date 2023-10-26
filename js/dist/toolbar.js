(function () {
	'use strict';

	var icons = {
		activity: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>',
		alertCircle: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
		alertTriangle: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
		arrowRight: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
		check: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>',
		database: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-database"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>',
		disc: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-disc"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>',
		edit2: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>',
		image: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
		layers: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>',
		mail: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
		map: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>',
		paperclip: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-paperclip"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>',
		zap: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>'
	};

	class Request
	{
		constructor(data) {
			this.id = data.id;

			this.responseDuration = Math.round(parseFloat(data.responseDuration));
			this.memoryUsage = this.formatBytes(data.memoryUsage);

			this.cache = this.resolveCache(data);
			this.database = this.resolveDatabase(data);
			this.events = this.resolveEvents(data);
			this.log = this.resolveLog(data);
			this.models = this.resolveModels(data);
			this.notifications = this.resolveNotifications(data);
			this.queueJobs = this.resolveQueueJobs(data);
			this.redisCommands = this.resolveRedisCommands(data);
			this.views = this.resolveViews(data);

			this.performanceMetrics = this.resolvePerformanceMetrics(data);
			this.clientMetrics = this.resolveClientMetrics(data);
			this.webVitals = this.resolveWebVitals(data);

			this.errorsCount = this.getErrorsCount(data);
			this.warningsCount = this.getWarningsCount(data);
		}

		isClientError() {
			return this.responseStatus >= 400 && this.responseStatus < 500
		}

		isServerError() {
			return this.responseStatus >= 500 && this.responseStatus < 600
		}

		resolveCache(data) {
			let deletes = parseInt(data.cacheDeletes);
			let hits = parseInt(data.cacheHits);
			let reads = parseInt(data.cacheReads);
			let writes = parseInt(data.cacheWrites);

			return {
				queries: {
					deletes, hits, reads, writes,
					total: deletes + hits + reads + writes
				},
				time: data.cacheTime
			}
		}

		resolveDatabase(data) {
			let queries = this.enforceArray(data.databaseQueries);

			let total = parseInt(data.databaseQueriesCount) || queries.length;
			let slow = parseInt(data.databaseSlowQueries)
				|| queries.filter(query => query.tags && query.tags.includes('slow')).length;
			let selects = parseInt(data.databaseSelects)
				|| queries.filter(query => query.query.match(/^select /i)).length;
			let inserts = parseInt(data.databaseInserts)
				|| queries.filter(query => query.query.match(/^insert /i)).length;
			let updates = parseInt(data.databaseUpdates)
				|| queries.filter(query => query.query.match(/^update /i)).length;
			let deletes = parseInt(data.databaseDeletes)
				|| queries.filter(query => query.query.match(/^delete /i)).length;
			let others = parseInt(data.databaseOthers)
				|| queries.filter(query => ! query.query.match(/^(select|insert|update|delete) /i)).length;

			return {
				queries: { total, slow, selects, inserts, updates, deletes, others },
				time: Math.round(data.databaseDuration)
			}
		}

		resolveEvents(data) {
			return this.enforceArray(data.events).length
		}

		resolveLog(data) {
			return this.enforceArray(data.log).length
		}

		resolveModels(data) {
			let retrieved = Object.values(data.modelsRetrieved).reduce((sum, count) => sum + count, 0);
			let created = Object.values(data.modelsCreated).reduce((sum, count) => sum + count, 0);
			let updated = Object.values(data.modelsUpdated).reduce((sum, count) => sum + count, 0);
			let deleted = Object.values(data.modelsDeleted).reduce((sum, count) => sum + count, 0);

			return {
				retrieved, created, updated, deleted,
				total: retrieved + created + updated + deleted
			}
		}

		resolveNotifications(data) {
			return this.enforceArray(data.notifications).length
				+ Object.values(this.optionalNonEmptyObject(data.emailsData, {})).length
		}

		resolveQueueJobs(data) {
			return this.enforceArray(data.queueJobs).length
		}

		resolveRedisCommands(data) {
			return this.enforceArray(data.redisCommands).length
		}

		resolveViews(data) {
			return Object.values(this.optionalNonEmptyObject(data.viewsData, {})).length
		}

		resolveClientMetrics(data) {
			data = data.clientMetrics || {};

			return [
				{ name: 'Redirect', value: data.redirect },
				{ name: 'DNS', value: data.dns, color: 'purple', onChart: true },
				{ name: 'Connection', value: data.connection, color: 'blue', onChart: true },
				{ name: 'Waiting', value: data.waiting, color: 'red', onChart: true },
				{ name: 'Receiving', value: data.receiving, color: 'green', onChart: true },
				{ name: 'To interactive', value: data.domInteractive, color: 'blue', onChart: true, dom: true },
				{ name: 'To complete', value: data.domComplete, color: 'purple', onChart: true, dom: true }
			]
		}

		resolvePerformanceMetrics(data) {
			data = data.performanceMetrics;

			if (! data) {
				return [
					{ name: 'App', value: (this.responseDuration) - (this.database.time) - (this.cache.time), color: 'blue' },
					{ name: 'DB', value: this.database.time, color: 'red' },
					{ name: 'Cache', value: this.cache.time, color: 'green' }
				].filter(metric => metric.value > 0)
			}

			data = data.filter(metric => metric instanceof Object)
				.map((metric, index) => {
					metric.color = metric.color || 'purple';
					return metric
				});

			let metricsSum = data.reduce((sum, metric) => { return sum + metric.value }, 0);

			data.push({ name: 'Other', value: this.responseDuration - metricsSum, color: 'purple' });

			return data
		}

		resolveWebVitals(data) {
			data = data.webVitals;

			let vitals = {
				cls: { slow: 7300, moderate: 3800 },
				fid: { slow: 300, moderate: 100 },
				lcp: { slow: 4000, moderate: 2000 },
				fcp: { slow: 4000, moderate: 2000 },
				ttfb: { slow: 600, moderate: 600 },
				si: { slow: 5800, moderate: 4300 }
			};

			Object.keys(vitals).forEach(key => {
				let value = data[key];
				let score = 'fast';
				let available = value !== undefined;

				if (value > vitals[key].slow) score = 'slow';
				else if (value > vitals[key].moderate) score = 'moderate';

				data[key] = { value, score, available };
			});

			return data
		}

		getErrorsCount(data) {
			return data.log.reduce((count, message) => {
				return message.level == 'error' ? count + 1 : count
			}, 0)
		}

		getWarningsCount(data) {
			return data.log.filter(message => message.level == 'warning').length
				+ this.database.queries.slow
		}

		formatBytes(bytes) {
			let units = [ 'B', 'kB', 'MB', 'GB', 'TB', 'PB' ];
			let pow = Math.floor(Math.log(bytes) / Math.log(1024));

			return `${Math.round(bytes / Math.round(Math.pow(1024, pow)))} ${units[pow]}`
		}

		enforceArray(input) {
			return input instanceof Array ? input : []
		}

		optionalNonEmptyObject(input, defaultValue) {
			return input instanceof Object && Object.keys(input).filter(key => key != '__type__').length ? input : defaultValue
		}
	}

	var styles = `
.clockwork-toolbar {
	align-items: center;
	background: #fcfcfd;
	bottom: 0;
	box-sizing: border-box;
	color: #000;
	cursor: default;
	display: flex;
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
	font-size: 13px;
	font-weight: normal;
	height: 32px;
	left: 0;
	position: fixed;
	width: 100%;
	z-index: 9999;
}

.clockwork-toolbar * {
	box-sizing: border-box;
}

.clockwork-toolbar-status {
	align-items: center;
	display: flex;
	height: 100%;
	justify-content: center;
	width: 32px;
}

.clockwork-toolbar-status.success {
    background: hsl(109, 52%, 45%);
    color: #fff;
}

.clockwork-toolbar-status.warning {
    background: hsl(33, 87%, 47%);
    color: #fff;
}

.clockwork-toolbar-status.error {
    background: hsl(359, 57%, 55%);
    color: #fff;
}

.clockwork-toolbar-section {
	align-items: center;
	border-right: 1px solid #d1d1e0;
	border-right: 1px solid #f3f3f3;
	display: flex;
	height: 100%;
	padding: 0 14px;
	position: relative;
}

.clockwork-toolbar-section-value {
	color: #258cdb;
	font-size: 95%;
	font-weight: 600;
	padding-left: 10px;
}

.clockwork-toolbar-section-popover {
	bottom: 100%;
	display: none;
	left: 50%;
	padding-bottom: 17px;
	position: absolute;
    transform: translateX(-50%);
	z-index: 666;
}

.clockwork-toolbar-section:hover .clockwork-toolbar-section-popover {
	display: block;
}

.clockwork-toolbar-section-popover-content {
	background: hsl(240, 20%, 99%);
	border-radius: 8px;
	box-shadow: 0 1px 5px rgba(33, 33, 33, 0.4);
	font-size: 90%;
	max-height: 400px;
	overflow: auto;
	width: 100%;
}

.clockwork-toolbar-section-popover-content:before, .clockwork-toolbar-section-popover-content:after {
	border-style: solid;
	content: '';
	height: 0;
	position: absolute;
	width: 0;
}

.clockwork-toolbar-section-popover-content:before {
	border-color: hsl(240, 20%, 99%) transparent transparent transparent;
	border-width: 11px 11px 0 11px;
	bottom: 7px;
	left: calc(50% - 10px);
	z-index: 500;
}

.clockwork-toolbar-section-popover-content:after {
	border-color: rgba(33, 33, 33, 0.06) transparent transparent transparent;
	border-width: 12px 12px 0 12px;
	bottom: 5px;
	left: calc(50% - 11px);
}

.clockwork-toolbar-section-popover.left-aligned {
    transform: translateX(-35px);
}

.clockwork-toolbar-section-popover.left-aligned .clockwork-toolbar-section-popover-content:before {
	left: 35px;
	right: auto;
}

.clockwork-toolbar-section-popover.left-aligned .clockwork-toolbar-section-popover-content:after {
	left: 34px;
	right: auto;
}

.clockwork-toolbar-section-popover-title {
	align-items: center;
	color: #404040;
	display: flex;
    padding: 8px 12px;
}

.clockwork-toolbar-section-popover-title .feather {
	margin-right: 5px;
}

.clockwork-toolbar-section-popover-values {
    align-items: center;
	border-top: 1px solid #e7e7ef;
    display: flex;
}

.clockwork-toolbar-section-popover-value {
	border-right: 1px solid #e7e7ef;
    cursor: default;
    padding: 8px 12px 8px;
}

.clockwork-toolbar-section-popover-value .value {
    color: #258cdb;
    font-size: 135%;
    margin-bottom: 3px;
    white-space: nowrap;
}

.clockwork-toolbar-section-popover-value .title {
    color: #777;
    font-size: 85%;
    text-transform: uppercase;
    white-space: nowrap;
}

.clockwork-toolbar-section-popover-value .title.has-mark:before {
	border-radius: 50%;
	content: '';
	display: inline-block;
	height: 7px;
	margin-right: 3px;
	width: 7px;
}

.clockwork-toolbar-section-popover-value .title.mark-blue:before {
	background: hsl(212, 89%, 55%);
}

.clockwork-toolbar-section-popover-value .title.mark-red:before {
	background: hsl(359, 57%, 55%);
}

.clockwork-toolbar-section-popover-value .title.mark-green:before {
	background: hsl(109, 52%, 45%);
}

.clockwork-toolbar-section-popover-value .title.mark-purple:before {
	background: hsl(273, 57%, 55%);
}

.clockwork-toolbar-section-popover-value .title.mark-grey:before {
	background: hsl(240, 5, 27);
}

.clockwork-toolbar-details {
	align-items: center;
	display: flex;
	height: 100%;
	margin-left: auto;
}

.clockwork-toolbar-details-label {
	font-size: 85%;
}

.clockwork-toolbar-details-button {
	align-items: center;
	background: hsl(30, 1%, 96%);
	background: #2786f3;
	color: dimgrey;
	color: #fff;
	display: flex;
	height: 100%;
	justify-content: center;
	margin-left: 8px;
	text-decoration: none;
	width: 32px;
}

.clockwork-toolbar-performance-chart {
	position: absolute;
	left: 0;
	top: 0;
	height: 1px;
	display: flex;
	width: calc(100% - 64px);
	left: 32px;
}

.clockwork-toolbar-performance-chart .bar {
	height: 100%;
}

.clockwork-toolbar-performance-chart .bar.blue {
	background: hsl(212, 89%, 55%);
}

.clockwork-toolbar-performance-chart .bar.red {
	background: hsl(359, 57%, 55%);
}

.clockwork-toolbar-performance-chart .bar.green {
	background: hsl(109, 52%, 45%);
}

.clockwork-toolbar-performance-chart .bar.purple {
	background: hsl(273, 57%, 55%);
}

.clockwork-toolbar-performance-chart .bar.grey {
	background: hsl(240, 5, 27);
}

.clockwork-toolbar-performance-chart.chart-client {
	bottom: 0;
	top: inherit;
}

.clockwork-toolbar .feather {
	display: inline-block;
	height: 1em;
	width: 1em;
	vertical-align: -0.125em;
}

.clockwork-toolbar .feather svg {
	display: block;
	height: 100%;
	width: 100%;
}
`;

	class Toolbar
	{
		constructor(payload) {
			payload = { ...this.payload(), ...payload };

			this.enabled = payload.toolbar;
			this.requestId = payload.requestId;
			this.path = payload.path || '/__clockwork/';
			this.webPath = payload.webPath || '/clockwork/app';
			this.cspNonce = payload.cspNonce || document.head.querySelector('meta[name="csp-nonce"]')?.getAttribute("content");
		}

		show(attempts = 0) {
			if (! this.enabled) return
			if (attempts > 3) return

			fetch(`${this.path}${this.requestId}`)
				.then(request => request.json())
				.then(request => {
					if (! Object.keys(request).length) {
						return setTimeout(() => this.show(attempts + 1), (attempts + 1) * (attempts + 1) * 100)
					}

					this.render(new Request(request));
				});
		}

		render(request) {
			let html = `
			<div class="clockwork-toolbar">
				${this.renderStatusSection(request)}

				${this.renderSection({
					name: 'Performance',
					icon: 'activity',
					values: [
						{ name: 'Response time', value: request.responseDuration, unit: 'ms' },
						{ name: 'Memory usage', value: request.memoryUsage }
					],
					popover: [
						{ name: 'Response time', value: request.responseDuration, unit: 'ms' },
						{ name: 'Memory', value: request.memoryUsage },
						...request.performanceMetrics.map(m => Object.assign({ unit: 'ms' }, m))
					],
					popoverClasses: 'left-aligned'
				})}

				${this.renderSection({
					name: 'Log',
					icon: 'edit2',
					values: [ { name: 'Messages', value: request.log } ]
				})}

				${this.renderSection({
					name: 'Events',
					icon: 'zap',
					values: [ { name: 'Events', value: request.events } ]
				})}

				${this.renderSection({
					name: 'Database',
					icon: 'database',
					values: [
						{ name: 'Queries', value: request.database.queries.total },
						{ name: 'Database time', value: request.database.time, unit: 'ms' },
					],
					popover: [
						{ name: 'Queries', value: request.database.queries.total },
						{ name: 'Slow', value: request.database.queries.slow },
						{ name: 'Selects', value: request.database.queries.selects },
						{ name: 'Inserts', value: request.database.queries.inserts },
						{ name: 'Updates', value: request.database.queries.updates },
						{ name: 'Deletes', value: request.database.queries.deletes },
						{ name: 'Others', value: request.database.queries.others },
						{ name: 'Time', value: request.database.time, unit: 'ms' }
					]
				})}

				${this.renderSection({
					name: 'Models',
					icon: 'disc',
					values: [ { name: 'Models', value: request.models.total } ],
					popover: [
						{ name: 'Retrieved', value: request.models.retrieved },
						{ name: 'Created', value: request.models.created },
						{ name: 'Updated', value: request.models.updated },
						{ name: 'Deleted', value: request.models.deleted }
					]
				})}

				${this.renderSection({
					name: 'Cache',
					icon: 'paperclip',
					values: [
						{ name: 'Queries', value: request.cache.queries.total },
						{ name: 'Cache time', value: request.cache.time, unit: 'ms' }
					],
					popover: [
						{ name: 'Queries', value: request.cache.queries.total },
						{ name: 'Reads', value: request.cache.queries.reads },
						{ name: 'Hits', value: request.cache.queries.hits },
						{ name: 'Writes', value: request.cache.queries.writes },
						{ name: 'Deletes', value: request.cache.queries.deletes },
						{ name: 'Time', value: request.cache.time, unit: 'ms' }
					]
				})}

				${this.renderSection({
					name: 'Redis',
					icon: 'layers',
					values: [ { name: 'Commands', value: request.redis } ]
				})}

				${this.renderSection({
					name: 'Queue',
					icon: 'clock',
					values: [ { name: 'Jobs', value: request.queue } ]
				})}

				${this.renderSection({
					name: 'Views',
					icon: 'image',
					values: [ { name: 'Views', value: request.views } ]
				})}

				${this.renderSection({
					name: 'Notifications',
					icon: 'mail',
					values: [ { name: 'Notifications', value: request.notifications } ]
				})}

				${this.renderSection({
					name: 'Routes',
					icon: 'map',
					values: [ { name: 'Routes', value: request.routes } ]
				})}

				<div class="clockwork-toolbar-details">
					<span class="clockwork-toolbar-details-label">
						Show details
					</span>
					<a href="${this.webPath}#${request.id}" target="_blank" class="clockwork-toolbar-details-button">
						${icons.arrowRight}
					</a>
				</div>

				${this.renderChart(request.performanceMetrics)}
				${this.renderChart(request.clientMetrics.filter(m => m.onChart), 'chart-client')}
			</div>
		`;

			this.appendStyles();

			let toolbar = document.createElement('div');
			toolbar.innerHTML = html;
			if (this.cspNonce) toolbar.nonce = this.cspNonce;

			document.querySelector('body').append(toolbar);
		}

		renderStatusSection(request) {
			let statusClass = r => {
				if (r.errorsCount || r.isServerError()) return 'error'
				if (r.warningsCount || r.isClientError()) return 'warning'
				return 'success'
			};

			let statusIcon = r => {
				if (r.errorsCount || r.isServerError()) return icons.alertCircle
				if (r.warningsCount || r.isClientError()) return icons.alertTriangle
				return icons.check
			};

			return `
			<div class="clockwork-toolbar-status ${statusClass(request)}">
				${statusIcon(request)}
			</div>
		`
		}

		renderSection(section) {
			let values = section.values.filter(v => v.value);
			let popover = section.popover ? section.popover.filter(v => v.value) : [];

			values.forEach(v => v.value = v.unit ? `${v.value}&nbsp;${v.unit}` : v.value);
			popover.forEach(v => v.value = v.unit ? `${v.value}&nbsp;${v.unit}` : v.value);

			if (! values.length) return ''

			return `
			<div class="clockwork-toolbar-section">
				<div class="clockwork-toolbar-section-icon" title="${section.name}">
					${icons[section.icon]}
				</div>
				${values.map(({ name, value }) => {
					return `<div class="clockwork-toolbar-section-value" title="${name}">${value}</div>`
				}).join('')}
				<div class="clockwork-toolbar-section-popover ${section.popoverClasses || ''}">
					<div class="clockwork-toolbar-section-popover-content">
						<div class="clockwork-toolbar-section-popover-title">${icons[section.icon]} ${section.name}</div>
						<div class="clockwork-toolbar-section-popover-values">
							${popover.map(({ name, value, color }) => {
								return `<div class="clockwork-toolbar-section-popover-value">
									<div class="value">${value}</div>
									<div class="title ${color ? `has-mark mark-${color}` : ''}">${name}</div>
								</div>`
							}).join('')}
						</div>
					</div>
				</div>
			</div>
		`
		}

		renderChart(metrics, classes = '') {
			let totalTime = metrics.reduce((sum, m) => sum + m.value, 0);

			return `
			<div class="clockwork-toolbar-performance-chart ${classes}">
				${metrics.map(m => {
					return `<div class="bar ${m.color}" title="${m.name}" style="width:${m.value / totalTime * 100}%"></div>`
				}).join('')}
			</div>
		`
		}

		appendStyles() {
			let style = document.createElement('style');
			style.innerHTML = styles;
			if (this.cspNonce) style.nonce = this.cspNonce;

			document.querySelector('body').append(style);
		}

		payload() {
			let matches = document.cookie.match(/(?:^| )x-clockwork=([^;]*)/);

			return matches ? JSON.parse(decodeURIComponent(matches[1])) : {}
		}
	}

	let toolbar = new Toolbar;

	toolbar.show();

})();
