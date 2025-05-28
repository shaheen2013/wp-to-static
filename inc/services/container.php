<?php

namespace MW_STATIC\Inc\Services;

use Exception;
use ReflectionClass;

class Container
{

  protected $instances = [];

  protected $shared = [];

  protected function bind($abstract, $concrete = null, $shared = false)
  {

    if ($concrete === null) {

      $concrete = $abstract;
    }

    if ($shared) {

      // Ensure shared instances are stored as arrays with 'instance' key

      $this->shared[$abstract] = [

        'concrete' => $concrete,

        'instance' => null

      ];
    } else {

      $this->instances[$abstract] = $concrete;
    }
  }

  protected function resolve($abstract)

  {

    if (isset($this->shared[$abstract])) {

      return $this->getSharedInstance($abstract);
    }

    if (isset($this->instances[$abstract])) {

      $concrete = $this->instances[$abstract];

      return $this->build($concrete);
    }



    if (class_exists($abstract)) {

      $this->bind($abstract, $abstract, false);

      return $this->build($abstract);
    }

    throw new Exception(sprintf('Class %s not found.', esc_html($abstract)));
  }

  protected function getSharedInstance($abstract)

  {

    // Ensure $this->shared[$abstract] is an array before accessing 'instance'

    if (!isset($this->shared[$abstract]['instance'])) {

      // Initialize as an array if it's not

      if (!is_array($this->shared[$abstract])) {

        $this->shared[$abstract] = [];
      }

      // Build the shared instance and store it in 'instance'

      $this->shared[$abstract]['instance'] = $this->build($this->shared[$abstract]['concrete']);
    }

    return $this->shared[$abstract]['instance'];
  }

  protected function build($concrete)

  {

    $reflector = new ReflectionClass($concrete);

    if ($constructor = $reflector->getConstructor()) {

      $parameters = $constructor->getParameters();

      $dependencies = [];

      foreach ($parameters as $parameter) {

        $dependency = $parameter->getType();

        if ($dependency && !$dependency->isBuiltin()) {

          $dependencies[] = $this->resolve($dependency->getName());
        } else if ($parameter->isDefaultValueAvailable()) {

          $dependencies[] = $parameter->getDefaultValue();
        } else {

          throw new Exception(
            sprintf(
                'Cannot resolve dependency for %s.',
                esc_html($parameter->getName())
            )
          );
        
        }
      }

      return $reflector->newInstanceArgs($dependencies);
    }

    return $reflector->newInstance();
  }
}
