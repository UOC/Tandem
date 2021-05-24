var initializeRatingControls = function() {
    console.log('Function not ready yet!');
};

$(function() {
    var hexToRGB = function(hex) {
        // Expand shorthand form to full form
        var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
        hex = hex.replace(shorthandRegex, function(m, r, g, b) {
            return r + r + g + g + b + b;
        });
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? [
            parseInt(result[1], 16),
            parseInt(result[2], 16),
            parseInt(result[3], 16)
        ] : null;
    };

    var splitString = function(value) {
        var re = /-?\d*\.?\d+/g;
        var toStr = function toStr(val) {
            return typeof val === "string" ? val : String(val);
        };
        return {
            digits: toStr(value).match(re).map(Number),
            nondigits: toStr(value).split(re)
        };
    };

    var interpolatedValue = function(startValue, endValue, progress) {
        return (endValue - startValue) * progress + startValue;
    };

    var interpolatedArray = function(startArray, endArray, progress) {
        return startArray.map(function(startValue, index) {
            return (endArray[index] - startValue) * progress + startValue;
        });
    };

    var interpolatedColor = function(startColor, endColor, progress) {
        var interpolatedRGB = interpolatedArray(startColor, endColor, progress).map(function(channel) {
            return Math.round(channel);
        });
        return "rgba(" + interpolatedRGB[0] + "," + interpolatedRGB[1] + "," + interpolatedRGB[2] + ",1)";
    };

    var recomposeString = function(digits, nondigits) {
        return nondigits.reduce(function (a, b, i) {
            return a + digits[i - 1] + b;
        });
    };

    var RatingControl = function(element) {
        var self = this;
        self.containerElement = element;
        self.selectedRatingElement = self.containerElement.querySelector(".current-rating");
        self.selectedRatingSVGContainer = self.selectedRatingElement.querySelector(".svg-wrapper");
        self.ratingElements = [].slice.call(self.containerElement.querySelectorAll(".rating-option")).map(function(element) {
            return {
                container: element,
                icon: element.querySelector(".icon"),
                label: element.querySelector(".label"),
                selectedFill: hexToRGB(element.getAttribute("selected-fill") || "#FFD885")
            };
        });
        self.selectedRating = '';
        self.sliderPosition = 0;
        self.facePaths = [];
        self.labelColor = hexToRGB("#ABB2B6");
        self.labelSelectedColor = hexToRGB("#313B3F");
        self.dragging = false;
        self.handleDragOffset = 0;
        self.ratingTouchStartPosition = {x:0, y:0};
        self.onRatingChange = function() {};
        self.easings = {
            easeInOutCubic: function(t, b, c, d) {
                if ((t/=d/2) < 1) return c/2*t*t*t + b;
                return c/2*((t-=2)*t*t + 2) + b;
            },
            linear: function (t, b, c, d) {
                return c*t/d + b;
            }
        };
        self.setRating = function(rating, options) {
            options = options || {};
            var startTime;
            var fireChange = options.fireChange || false;
            var onComplete = options.onComplete || function() {};
            var easing = options.easing || self.easings.easeInOutCubic;
            var duration = typeof options.duration === 'undefined' ? 550 : options.duration;
            var startXPosition = self.sliderPosition;
            var endXPosition = rating * $(self.selectedRatingElement).width();
            var valorationId = '#' + $(self.selectedRatingElement).parent().attr('id').replace('rating-control-', '');

            if (duration > 0) {
                var anim = function(timestamp) {
                    startTime = startTime || timestamp;
                    var elapsed = timestamp - startTime;
                    var progress = easing(elapsed, startXPosition, endXPosition - startXPosition, duration);
                    self.setSliderPosition(progress);
                    if (elapsed < duration) {
                        requestAnimationFrame(anim);
                    } else {
                        self.setSliderPosition(endXPosition);
                        self.setLabelTransitionEnabled(true);
                        if (self.onRatingChange && self.selectedRating !== rating && fireChange) {
                            self.onRatingChange(rating);
                        }
                        onComplete();
                        self.selectedRating = rating;
                    }
                };

                self.setLabelTransitionEnabled(false);
                requestAnimationFrame(anim);
            } else {
                self.setSliderPosition(endXPosition);
                if (self.onRatingChange && self.selectedRating !== rating && fireChange) {
                    self.onRatingChange(rating);
                }
                onComplete();
                self.selectedRating = rating;
            }

            $(valorationId).val(rating+1);
        };
        self.setSliderPosition = function(position) {
            self.sliderPosition = Math.min(Math.max(0, position), $(self.containerElement).width() - $(self.selectedRatingElement).width());
            var stepProgress = self.sliderPosition / $(self.containerElement).width() * self.ratingElements.length;
            var relativeStepProgress = stepProgress - Math.floor(stepProgress);
            var currentStep = Math.round(stepProgress);
            var startStep = Math.floor(stepProgress);
            var endStep = Math.ceil(stepProgress);
            // Move handle
            self.selectedRatingElement.style.transform = "translateX(" + (self.sliderPosition / $(self.selectedRatingElement).width() * 100) + "%)";
            // Set face
            var startPaths = self.facePaths[startStep];
            var endPaths = self.facePaths[endStep];
            var interpolatedPaths = {};
            for (var featurePath in startPaths) {
                if (startPaths.hasOwnProperty(featurePath)) {
                    var startPath = startPaths[featurePath];
                    var endPath = endPaths[featurePath];
                    var interpolatedPoints = interpolatedArray(startPath.digits, endPath.digits, relativeStepProgress);
                    interpolatedPaths[featurePath] = recomposeString(interpolatedPoints, startPath.nondigits);
                }
            }
            var interpolatedFill = interpolatedColor(self.ratingElements[startStep]["selectedFill"], self.ratingElements[endStep]["selectedFill"], relativeStepProgress);
            self.selectedRatingSVGContainer.innerHTML = '<svg width="55px" height="55px" viewBox="0 0 50 50"><path d="M50,25 C50,38.807 38.807,50 25,50 C11.193,50 0,38.807 0,25 C0,11.193 11.193,0 25,0 C38.807,0 50,11.193 50,25" class="base" fill="' + interpolatedFill + '"></path><path d="' + interpolatedPaths["mouth"] + '" class="mouth" fill="#655F52"></path><path d="' + interpolatedPaths["right-eye"] + '" class="right-eye" fill="#655F52"></path><path d="' + interpolatedPaths["left-eye"] + '" class="left-eye" fill="#655F52"></path></svg>';
            // Update marker icon/label
            self.ratingElements.forEach(function(element, index) {
                var adjustedProgress = 1;
                if (index === currentStep) {
                    adjustedProgress = 1 - Math.abs((stepProgress - Math.floor(stepProgress) - 0.5) * 2);
                }
                element.icon.style.transform = "scale(" + adjustedProgress + ")";
                element.label.style.transform = "translateY(" + interpolatedValue(9, 0, adjustedProgress) + "px)";
                element.label.style.color = interpolatedColor(self.labelSelectedColor, self.labelColor, adjustedProgress);
            });
        };
        self.onHandleDrag = function(e) {
            e.preventDefault();
            if (e.touches) {
                e = e.touches[0];
            }
            var offset = $(self.selectedRatingElement).width() / 2 - self.handleDragOffset;
            var xPos = e.clientX - self.containerElement.getBoundingClientRect().left;
            self.setSliderPosition(xPos - $(self.selectedRatingElement).width() / 2 + offset);
        };
        self.onHandleRelease = function() {
            self.dragging = false;
            self.setLabelTransitionEnabled(true);
            var rating = Math.round(self.sliderPosition / $(self.containerElement).width() * self.ratingElements.length);
            self.setRating(rating, {duration: 200, fireChange: true});
        };
        self.setLabelTransitionEnabled = function(enabled) {
            self.ratingElements.forEach(function(element) {
                if (enabled) {
                    element.label.classList.remove("no-transition");
                } else {
                    element.label.classList.add("no-transition");
                }
            });
        };

        self.ratingElements.forEach(function(element) {
            // Copy face path data from HTML
            var paths = {};
            [].forEach.call(element.icon.querySelectorAll("path:not(.base)"), function(path) {
                var pathStr = path.getAttribute("d");
                paths[path.getAttribute("class")] = splitString(pathStr);
            });
            self.facePaths.push(paths);
            // On rating selected
            var eventName = "ontouchend" in document ? "ontouchend" : "click";
            $(element.container).unbind(eventName).bind(eventName, function(e) {
                if ("ontouchend" in document) {
                    var ratingTouchCurrentPosition = {x: e.pageX, y: e.pageY};
                    var dragDistance = Math.sqrt(Math.pow(ratingTouchCurrentPosition.x - self.ratingTouchStartPosition.x, 2) + Math.pow(ratingTouchCurrentPosition.y - self.ratingTouchStartPosition.y, 2));
                    if (dragDistance > 10) {
                        return;
                    }
                }
                var newRating = element.container.getAttribute("rating") - 1;
                self.setRating(newRating, {fireChange: true});
            });
        });

        if ("ontouchend" in document) {
            $(self).parent().unbind("touchstart").bind("touchstart", function(e) {
                if ($(e.target).hasClass("rating-option")) {
                    self.ratingTouchStartPosition = {x: e.touches[0].pageX, y: e.touches[0].pageY};
                }
            });
            $(self.selectedRatingElement).unbind("touchstart").bind("touchstart", function(e) {
                self.dragging = true;
                self.handleDragOffset = e.touches[0].pageX - self.selectedRatingElement.getBoundingClientRect().left;
                self.setLabelTransitionEnabled(false);
            });
            $(self.selectedRatingElement).unbind("touchmove").bind("touchmove", $.proxy(self.onHandleDrag, self));
            $(self.selectedRatingElement).unbind("touchend").bind("touchend", $.proxy(self.onHandleRelease, self));
        } else {
            $(self).parent().unbind("mousedown").bind("mousedown", function(e) {
                if (e.target === self.selectedRatingElement) {
                    e.preventDefault();
                    self.dragging = true;
                    self.handleDragOffset = e.offsetX;
                    self.setLabelTransitionEnabled(false);
                    document.body.classList.add("dragging");
                    document.body.addEventListener("mousemove", $.proxy(self.onHandleDrag, self));
                }
            });
            $(self).parent().unbind("mouseup").bind("mouseup", function(e) {
                if (self.dragging) {
                    document.body.classList.remove("dragging");
                    document.body.removeEventListener("mousemove", $.proxy(self.onHandleDrag, self));
                    $.proxy(self.onHandleRelease, self)(e);
                }
            });
        }

        // self.setRating(2, {duration: 0}); // Set initial value
    };

    $(".rating-control-container").addClass("clip-marker");

    initializeRatingControls = function() {
        var $ratingControls = $(".rating-control");
        $ratingControls.each(function() {
            new RatingControl(this);
        });
    };
});
